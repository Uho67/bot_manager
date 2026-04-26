<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\User\Controller;

use App\Mailout\Repository\PostMailoutRepository;
use App\User\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/users')]
class UserAdminController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PostMailoutRepository $postMailoutRepository,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    #[Route('/select-all-ids', name: 'api_users_select_all_ids', methods: ['GET'])]
    public function selectAllIds(Request $request): JsonResponse
    {
        $user = $this->getUserFromAuth();
        $botIdentifier = $user->getBotIdentifier();

        $params = $request->query->all();
        $ids = $this->userRepository->findAllIdsByBotIdentifierWithFilters($botIdentifier, $params);

        return new JsonResponse(['ids' => $ids]);
    }

    #[Route('/mass-delete', name: 'api_users_mass_delete', methods: ['POST'])]
    public function massDelete(Request $request): JsonResponse
    {
        $user = $this->getUserFromAuth();
        $botIdentifier = $user->getBotIdentifier();

        $data = json_decode($request->getContent(), true);

        if (!\is_array($data) || !isset($data['ids']) || !\is_array($data['ids'])) {
            return new JsonResponse(['error' => 'Invalid request. Expected "ids" array.'], 400);
        }

        if (empty($data['ids'])) {
            return new JsonResponse(['error' => 'No user IDs provided.'], 400);
        }

        $deleted = $this->userRepository->bulkDeleteUsers($botIdentifier, $data['ids']);

        return new JsonResponse([
            'message' => 'Users deleted successfully',
            'deleted' => $deleted,
        ]);
    }

    #[Route('/mass-send-post', name: 'api_users_mass_send_post', methods: ['POST'])]
    public function massSendPost(Request $request): JsonResponse
    {
        $adminUser = $this->getUserFromAuth();
        $botIdentifier = $adminUser->getBotIdentifier();

        $data = json_decode($request->getContent(), true);

        if (!\is_array($data) || !isset($data['ids']) || !\is_array($data['ids'])) {
            return new JsonResponse(['error' => 'Invalid request. Expected "ids" array.'], 400);
        }

        if (empty($data['ids'])) {
            return new JsonResponse(['error' => 'No user IDs provided.'], 400);
        }

        if (!isset($data['post_id']) || !\is_int($data['post_id'])) {
            return new JsonResponse(['error' => 'Invalid request. Expected integer "post_id".'], 400);
        }

        $users = $this->userRepository->findByIdsAndBotIdentifier($data['ids'], $botIdentifier);

        if (empty($users)) {
            return new JsonResponse(['message' => 'No matching users found', 'created' => 0]);
        }

        $mailouts = array_map(
            fn($user) => ['chat_id' => $user->getChatId(), 'post_id' => $data['post_id']],
            $users
        );

        $created = $this->postMailoutRepository->bulkInsertPostMailouts($botIdentifier, $mailouts);

        return new JsonResponse([
            'message' => 'Post mailout records created successfully',
            'created' => $created,
        ]);
    }

    #[Route('/import-csv', name: 'api_users_import_csv', methods: ['POST'])]
    public function importCsv(Request $request): JsonResponse
    {
        $user = $this->getUserFromAuth();
        $botIdentifier = $user->getBotIdentifier();

        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile) {
            return new JsonResponse(['error' => 'No file uploaded. Expected multipart field "file".'], 400);
        }

        if (strtolower($file->getClientOriginalExtension()) !== 'csv') {
            return new JsonResponse(['error' => 'Invalid file type. Only CSV files are accepted.'], 400);
        }

        $handle = fopen($file->getPathname(), 'r');
        if ($handle === false) {
            return new JsonResponse(['error' => 'Could not read uploaded file.'], 500);
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return new JsonResponse(['error' => 'CSV file is empty or invalid.'], 400);
        }

        // Normalize header column names
        $columns = array_map('trim', $header);
        $colIndex = array_flip($columns);

        $required = ['chat_id'];
        foreach ($required as $col) {
            if (!isset($colIndex[$col])) {
                fclose($handle);
                return new JsonResponse(['error' => "Missing required column: {$col}"], 400);
            }
        }

        $usersData = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            ++$rowNumber;

            $chatId = isset($colIndex['chat_id']) ? trim($row[$colIndex['chat_id']] ?? '') : '';
            if ($chatId === '') {
                continue;
            }

            $firstName = isset($colIndex['first_name']) ? trim($row[$colIndex['first_name']] ?? '') : '';
            $lastName  = isset($colIndex['last_name'])  ? trim($row[$colIndex['last_name']]  ?? '') : '';
            $name      = trim($firstName . ' ' . $lastName) ?: $chatId;

            $username = isset($colIndex['user_name']) ? trim($row[$colIndex['user_name']] ?? '') : '';

            $isBlocked = isset($colIndex['is_blocked']) ? trim($row[$colIndex['is_blocked']] ?? '0') : '0';
            $status    = $isBlocked === '1' ? 'blocked' : 'active';

            $createdAt = null;
            if (isset($colIndex['createdAt'])) {
                $ms = (int) ($row[$colIndex['createdAt']] ?? 0);
                if ($ms > 0) {
                    $createdAt = new \DateTimeImmutable('@' . (int) ($ms / 1000));
                }
            }

            $usersData[] = [
                'chat_id'    => $chatId,
                'name'       => $name,
                'username'   => $username,
                'status'     => $status,
                'created_at' => $createdAt,
            ];
        }

        fclose($handle);

        if (empty($usersData)) {
            return new JsonResponse(['error' => 'No valid rows found in CSV.'], 400);
        }

        $result = $this->userRepository->bulkInsertIgnoreUsers($botIdentifier, $usersData);

        return new JsonResponse([
            'message'  => 'Import completed',
            'total'    => count($usersData),
            'imported' => $result['imported'],
            'skipped'  => $result['skipped'],
            'errors'   => $result['errors'],
        ]);
    }

    private function getUserFromAuth()
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user || !\is_object($user) || !method_exists($user, 'getBotIdentifier')) {
            throw new \RuntimeException('Not authenticated');
        }

        return $user;
    }
}
