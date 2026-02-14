<template>
  <div class="p-4">
    <h1 class="text-xl font-bold mb-4">Posts</h1>
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white border border-gray-200 rounded-lg">
        <thead>
          <tr>
            <th class="px-4 py-2 border-b text-center">ID</th>
            <th class="px-4 py-2 border-b text-center">Name</th>
            <th class="px-4 py-2 border-b text-center">Template Type</th>
            <th class="px-4 py-2 border-b text-center">Status</th>
            <th class="px-4 py-2 border-b text-center">Image</th>
            <th class="px-4 py-2 border-b text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="post in posts" :key="post.id">
            <td class="px-4 py-2 border-b text-center">{{ post.id }}</td>
            <td class="px-4 py-2 border-b text-center">{{ post.name }}</td>
            <td class="px-4 py-2 border-b text-center">
              <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">{{ post.template_type }}</span>
            </td>
            <td class="px-4 py-2 border-b text-center">
              <span :class="post.enabled ? 'px-2 py-1 bg-green-100 text-green-800' : 'px-2 py-1 bg-red-100 text-red-800'" class="rounded text-sm">
                {{ post.enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </td>
            <td class="px-4 py-2 border-b text-center">
              <img v-if="post.image" :src="getImageUrl(post.image)" alt="Post Image" class="w-16 h-16 object-cover rounded mx-auto">
              <span v-else class="text-gray-400 text-sm">No image</span>
            </td>
            <td class="px-4 py-2 border-b text-center">
              <div class="relative inline-block text-left">
                <button @click="openDropdown(post.id)" class="px-2 py-1 bg-gray-100 rounded hover:bg-gray-200">Actions</button>
                <div v-if="dropdownOpen === post.id" class="absolute z-10 w-32 bg-white border rounded shadow-lg right-0 bottom-full mb-1">
                  <button @click="editPost(post.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Edit</button>
                  <button @click="deletePost(post.id)" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">Delete</button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <button @click="createPost" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create Post</button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';
import type { Post } from '../types/Post';

const posts = ref<Post[]>([]);
const dropdownOpen = ref<number|null>(null);
const router = useRouter();

const fetchPosts = async () => {
  const { data } = await api.get('/api/posts');
  posts.value = data['member'] || [];
};

const openDropdown = (id: number) => {
  dropdownOpen.value = dropdownOpen.value === id ? null : id;
};

const editPost = (id: number) => {
  router.push({ name: 'PostEdit', params: { id } });
};

const deletePost = async (id: number) => {
  if (confirm('Are you sure you want to delete this post?')) {
    await api.delete(`/api/posts/${id}`);
    fetchPosts();
  }
};

const createPost = () => {
  router.push({ name: 'PostCreate' });
};

const getImageUrl = (path: string) => {
  // If path already starts with http, return as is
  if (path.startsWith('http')) {
    return path;
  }
  // Otherwise, prepend the API base URL
  return `${import.meta.env.VITE_API_URL}${path}`;
};

onMounted(fetchPosts);
</script>
