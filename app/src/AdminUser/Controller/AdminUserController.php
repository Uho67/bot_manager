<?php

namespace App\AdminUser\Controller;

use App\Repository\AdminUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class AdminUserController extends AbstractController
{
    #[Route('/api/admin-users', name: 'admin_users_list', methods: ['GET'])]
    public function getList(AdminUserRepository $adminUserRepository): JsonResponse
    {
        $users = $adminUserRepository->findAll();

        $data = array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'admin_name' => $user->getAdminName(),
                'admin_password' => $user->getAdminPassword(),
                'bot_code' => $user->getBotCode(),
                'is_super' => $user->isSuper(),
            ];
        }, $users);

        return $this->json($data);
    }
}
