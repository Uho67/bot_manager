<template>
  <div class="p-4 max-w-4xl mx-auto">
    <h1 class="text-xl font-bold mb-4">{{ isEdit ? 'Edit Template' : 'Create Template' }}</h1>

    <div v-if="errorMessage" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
        </div>
        <div class="ml-3 flex-1">
          <h3 class="text-sm font-medium text-red-800">Error</h3>
          <p class="mt-1 text-sm text-red-700">{{ errorMessage }}</p>
        </div>
        <button @click="errorMessage = ''" class="ml-3 flex-shrink-0 text-red-400 hover:text-red-600">
          <span class="sr-only">Dismiss</span>
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>
      </div>
    </div>

    <form @submit.prevent="submitForm" class="space-y-6">
      <div class="bg-white p-6 rounded-lg border border-gray-200">
        <h2 class="text-lg font-semibold mb-4">Basic Information</h2>

        <div class="space-y-4">
          <div>
            <label class="block mb-1 font-medium">Name</label>
            <input v-model="form.name" maxlength="100" required class="w-full border rounded px-3 py-2" placeholder="e.g., Main Menu Template" />
            <div class="text-xs text-gray-500">Template name (max 100 characters)</div>
          </div>

          <div>
            <label class="block mb-1 font-medium">Type</label>
            <select v-model="form.type" required class="w-full border rounded px-3 py-2">
              <option value="">Select type...</option>
              <option value="post">Post</option>
              <option value="start">Start</option>
              <option value="category">Category</option>
              <option value="product">Product</option>
            </select>
            <div class="text-xs text-gray-500">Template type defines where it will be used</div>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg border border-gray-200">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold">Button Layout</h2>
          <button type="button" @click="addLine" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
            + Add Line
          </button>
        </div>

        <div v-if="form.layout.length === 0" class="text-center py-8 text-gray-500">
          <p>No lines added yet. Click "Add Line" to start building your template.</p>
        </div>

        <div v-else class="space-y-4">
          <div v-for="(line, lineIndex) in form.layout" :key="lineIndex" class="border border-gray-300 rounded-lg p-4 bg-gray-50">
            <div class="flex justify-between items-center mb-3">
              <h3 class="font-medium text-gray-700">Line {{ lineIndex + 1 }}</h3>
              <div class="flex gap-2">
                <button type="button" @click="addButtonToLine(lineIndex)" :disabled="line.length >= 8" class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs disabled:opacity-50 disabled:cursor-not-allowed">
                  + Add Button
                </button>
                <button type="button" @click="removeLine(lineIndex)" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">
                  Remove Line
                </button>
              </div>
            </div>

            <div v-if="line.length === 0" class="text-center py-4 text-gray-400 text-sm">
              No buttons in this line. Click "Add Button" to add one.
            </div>

            <div v-else class="space-y-2">
              <div v-for="buttonIndex in line.length" :key="buttonIndex" class="flex items-center gap-2 bg-white p-2 rounded border border-gray-200">
                <span class="text-sm font-medium text-gray-600 w-8">{{ buttonIndex }}.</span>
                <select v-model="form.layout[lineIndex][buttonIndex - 1]" required class="flex-1 border rounded px-2 py-1 text-sm">
                  <option value="">Select button...</option>
                  <option v-for="button in availableButtons" :key="button.id" :value="button.id">
                    {{ button.label }} ({{ button.code }})
                  </option>
                </select>
                <button type="button" @click="moveButtonUp(lineIndex, buttonIndex - 1)" :disabled="buttonIndex === 1" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300 text-xs disabled:opacity-50 disabled:cursor-not-allowed">
                  ↑
                </button>
                <button type="button" @click="moveButtonDown(lineIndex, buttonIndex - 1)" :disabled="buttonIndex === line.length" class="px-2 py-1 bg-gray-200 rounded hover:bg-gray-300 text-xs disabled:opacity-50 disabled:cursor-not-allowed">
                  ↓
                </button>
                <button type="button" @click="removeButton(lineIndex, buttonIndex - 1)" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">
                  Remove
                </button>
              </div>
              <div v-if="line.length >= 8" class="text-xs text-orange-600 mt-2">
                ⚠ Maximum 8 buttons per line reached
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex gap-2 mt-4">
        <button type="submit" :disabled="isSubmitting || !isFormValid" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
          {{ isSubmitting ? 'Saving...' : 'Save' }}
        </button>
        <button type="button" @click="goBack" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../api';
import type { Button } from '../types/Button';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);

const form = ref<{
  name: string;
  type: string;
  layout: number[][];
}>({
  name: '',
  type: '',
  layout: [],
});

const availableButtons = ref<Button[]>([]);
const errorMessage = ref('');
const isSubmitting = ref(false);

const isFormValid = computed(() => {
  if (!form.value.name || !form.value.type) return false;
  if (form.value.layout.length === 0) return false;

  for (const line of form.value.layout) {
    if (line.length === 0) return false;
    for (const buttonId of line) {
      if (!buttonId || buttonId === 0) return false;
    }
  }

  return true;
});

const fetchButtons = async () => {
  try {
    const { data } = await api.get('/api/buttons');
    availableButtons.value = data['member'] || [];
  } catch (error: any) {
    errorMessage.value = 'Failed to load buttons';
  }
};

const fetchTemplate = async () => {
  if (isEdit.value) {
    try {
      const { data } = await api.get(`/api/templates/${route.params.id}`);
      form.value = {
        name: data.name,
        type: data.type,
        layout: data.layout || [],
      };
    } catch (error: any) {
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || 'Failed to load template';
    }
  } else if (route.query.duplicateFrom) {
    // Handle duplicate functionality
    try {
      const { data } = await api.get(`/api/templates/${route.query.duplicateFrom}`);
      form.value = {
        name: data.name + ' (Copy)',
        type: data.type,
        layout: data.layout || [],
      };
    } catch (error: any) {
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || 'Failed to load template for duplication';
    }
  }
};

const addLine = () => {
  form.value.layout.push([]);
};

const removeLine = (lineIndex: number) => {
  form.value.layout.splice(lineIndex, 1);
};

const addButtonToLine = (lineIndex: number) => {
  if (form.value.layout[lineIndex].length < 8) {
    form.value.layout[lineIndex].push(0);
  }
};

const removeButton = (lineIndex: number, buttonIndex: number) => {
  form.value.layout[lineIndex].splice(buttonIndex, 1);
};

const moveButtonUp = (lineIndex: number, buttonIndex: number) => {
  if (buttonIndex > 0) {
    const temp = form.value.layout[lineIndex][buttonIndex];
    form.value.layout[lineIndex][buttonIndex] = form.value.layout[lineIndex][buttonIndex - 1];
    form.value.layout[lineIndex][buttonIndex - 1] = temp;
  }
};

const moveButtonDown = (lineIndex: number, buttonIndex: number) => {
  if (buttonIndex < form.value.layout[lineIndex].length - 1) {
    const temp = form.value.layout[lineIndex][buttonIndex];
    form.value.layout[lineIndex][buttonIndex] = form.value.layout[lineIndex][buttonIndex + 1];
    form.value.layout[lineIndex][buttonIndex + 1] = temp;
  }
};

const submitForm = async () => {
  errorMessage.value = '';
  isSubmitting.value = true;

  try {
    if (isEdit.value) {
      await api.put(`/api/templates/${route.params.id}`, form.value);
    } else {
      await api.post('/api/templates', form.value);
    }
    router.push({ name: 'TemplateList' });
  } catch (error: any) {
    const apiError = error.response?.data;
    errorMessage.value = apiError?.description || apiError?.detail || apiError?.title || 'An error occurred while saving the template';
    console.error('API Error:', error.response?.data);
  } finally {
    isSubmitting.value = false;
  }
};

const goBack = () => {
  router.push({ name: 'TemplateList' });
};

onMounted(async () => {
  await fetchButtons();
  await fetchTemplate();
});
</script>

