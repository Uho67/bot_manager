<template>
  <div class="p-4 max-w-lg mx-auto">
    <h1 class="text-xl font-bold mb-4">{{ isEdit ? 'Edit Category' : 'Create Category' }}</h1>

    <!-- Error Message Display -->
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

    <form @submit.prevent="submitForm" class="space-y-4">
      <input v-if="isEdit" type="hidden" name="id" :value="form.id" />
      <div>
        <label class="block mb-1 font-medium">Name</label>
        <input v-model="form.name" maxlength="50" required class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block mb-1 font-medium">Is Root</label>
        <select v-model="form.isRoot" class="w-full border rounded px-3 py-2">
          <option :value="false">No</option>
          <option :value="true">Yes</option>
        </select>
      </div>
      <div>
        <label class="block mb-1 font-medium">Children Categories</label>
        <select v-model="form.childCategories" multiple class="w-full border rounded px-3 py-2">
          <option v-for="cat in allCategories" :key="cat.id" :value="`/api/categories/${cat.id}`">{{ cat.name }}</option>
        </select>
        <div class="text-xs text-gray-500">Select up to 10 children</div>
      </div>
      <div>
        <label class="block mb-1 font-medium">Image</label>
        <input type="file" accept="image/*" @change="onImageChange" class="w-full border rounded px-3 py-2" />
        <div v-if="imagePreview" class="mt-2">
          <img :src="imagePreview" alt="Preview" class="max-h-32 rounded border" />
        </div>
      </div>

      <!-- Button Layout Section -->
      <div class="bg-white p-6 rounded-lg border border-gray-200 mt-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold">Button Layout</h2>
          <button type="button" @click="addLine" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
            + Add Line
          </button>
        </div>

        <div v-if="!form.layout || form.layout.length === 0" class="text-center py-8 text-gray-500">
          <p>No lines added yet. Click "Add Line" to start building your category layout.</p>
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
                <select v-model="form.layout![lineIndex][buttonIndex - 1]" required class="flex-1 border rounded px-2 py-1 text-sm">
                  <option value="">Select button...</option>
                  <optgroup label="Regular Buttons">
                    <option v-for="button in availableButtons" :key="`button_${button.id}`" :value="`button_${button.id}`">
                      {{ button.label }} ({{ button.code }})
                    </option>
                  </optgroup>
                  <optgroup label="Child Categories">
                    <option v-for="child in currentChildCategories" :key="`category_${child.id}`" :value="`category_${child.id}`">
                      {{ child.name }} (Category)
                    </option>
                  </optgroup>
                  <optgroup label="Category Products">
                    <option v-for="product in categoryProducts" :key="`product_${product.id}`" :value="`product_${product.id}`">
                      {{ product.name }} (Product)
                    </option>
                  </optgroup>
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
        <button type="submit" :disabled="isSubmitting" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
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

interface Category {
  id: number;
  name: string;
}

interface Product {
  id: number;
  name: string;
}

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const form = ref<{
  id?: string;
  name: string;
  childCategories: string[];
  isRoot?: boolean;
  image?: string;
  layout?: (string | number)[][];
}>({
  id: '',
  name: '',
  childCategories: [],
  isRoot: false,
  image: '',
  layout: [],
});
const allCategories = ref<Category[]>([]);
const availableButtons = ref<Button[]>([]);
const categoryProducts = ref<Product[]>([]);
const imagePreview = ref<string | null>(null);
const errorMessage = ref('');
const isSubmitting = ref(false);

// Computed property to get current child categories for buttons dropdown
const currentChildCategories = computed(() => {
  return form.value.childCategories
    .map(catUri => {
      const id = parseInt(catUri.split('/').pop() || '0');
      return allCategories.value.find(c => c.id === id);
    })
    .filter(c => c !== undefined) as Category[];
});

const fetchAllCategories = async () => {
  try {
    const { data } = await api.get('/api/categories');
    allCategories.value = data['member'] || [];
  } catch (error: any) {
    errorMessage.value = error.response?.data?.description || error.response?.data?.detail || 'Failed to load categories';
  }
};

const fetchButtons = async () => {
  try {
    const { data } = await api.get('/api/buttons');
    availableButtons.value = data['member'] || [];
  } catch (error: any) {
    errorMessage.value = 'Failed to load buttons';
  }
};

const fetchCategoryProducts = async (categoryId: number | string) => {
  try {
    const { data } = await api.get(`/api/categories/${categoryId}`);
    categoryProducts.value = data.products || [];
  } catch (error: any) {
    console.error('Failed to load category products:', error);
  }
};

const fetchCategory = async () => {
  if (isEdit.value) {
    try {
      const { data } = await api.get(`/api/categories/${route.params.id}`);
      form.value = {
        id: data.id,
        name: data.name,
        childCategories: (data.childCategories || []).map((cat: Category) => `/api/categories/${cat.id}`),
        isRoot: data.isRoot || false,
        image: data.image || '',
        layout: data.layout || [],
      };
      // Set image preview with full URL if image exists
      if (data.image) {
        imagePreview.value = data.image.startsWith('http')
          ? data.image
          : `${import.meta.env.VITE_API_URL}${data.image}`;
      }
      // Fetch products for this category
      if (typeof route.params.id === 'string') {
        await fetchCategoryProducts(route.params.id);
      }
    } catch (error: any) {
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || 'Failed to load category';
    }
  }
};

const onImageChange = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    const file = target.files[0];

    // Show preview immediately
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);

    // Upload file to backend
    try {
      const formData = new FormData();
      formData.append('image', file);

      const response = await api.post('/api/category/upload-image', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      if (response.data.success) {
        form.value.image = response.data.path;
      } else {
        errorMessage.value = 'Failed to upload image';
      }
    } catch (error: any) {
      console.error('Image upload error:', error);
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || 'Failed to upload image';
    }
  }
};

const submitForm = async () => {
  if (form.value.childCategories.length > 10) {
    errorMessage.value = 'You can select up to 10 children.';
    return;
  }

  errorMessage.value = '';
  isSubmitting.value = true;

  try {
    if (isEdit.value) {
      await api.put(`/api/categories/${route.params.id}`, form.value);
    } else {
      await api.post('/api/categories', form.value);
    }
    await router.push({name: 'CategoryList'});
  } catch (error: any) {
    // Extract error message from API Platform error response
    const apiError = error.response?.data;
    errorMessage.value = apiError?.description || apiError?.detail || apiError?.title || 'An error occurred while saving the category';
    console.error('API Error:', error.response?.data);
  } finally {
    isSubmitting.value = false;
  }
};

const goBack = () => {
  router.push({ name: 'CategoryList' });
};

const addLine = () => {
  if (!form.value.layout) {
    form.value.layout = [];
  }
  form.value.layout.push([]);
};

const removeLine = (lineIndex: number) => {
  if (form.value.layout) {
    form.value.layout.splice(lineIndex, 1);
  }
};

const addButtonToLine = (lineIndex: number) => {
  if (form.value.layout && form.value.layout[lineIndex].length < 8) {
    form.value.layout[lineIndex].push(0);
  }
};

const removeButton = (lineIndex: number, buttonIndex: number) => {
  if (form.value.layout) {
    form.value.layout[lineIndex].splice(buttonIndex, 1);
  }
};

const moveButtonUp = (lineIndex: number, buttonIndex: number) => {
  if (form.value.layout && buttonIndex > 0) {
    const temp = form.value.layout[lineIndex][buttonIndex];
    form.value.layout[lineIndex][buttonIndex] = form.value.layout[lineIndex][buttonIndex - 1];
    form.value.layout[lineIndex][buttonIndex - 1] = temp;
  }
};

const moveButtonDown = (lineIndex: number, buttonIndex: number) => {
  if (form.value.layout && buttonIndex < form.value.layout[lineIndex].length - 1) {
    const temp = form.value.layout[lineIndex][buttonIndex];
    form.value.layout[lineIndex][buttonIndex] = form.value.layout[lineIndex][buttonIndex + 1];
    form.value.layout[lineIndex][buttonIndex + 1] = temp;
  }
};


onMounted(async () => {
  await fetchAllCategories();
  await fetchButtons();
  await fetchCategory();
});
</script>
