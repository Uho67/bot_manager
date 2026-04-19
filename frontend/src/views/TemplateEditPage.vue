<template>
  <div class="p-4 max-w-4xl mx-auto">
    <h1 class="page-title">{{ isEdit ? t('templates.edit_title') : t('templates.create_title') }}</h1>

    <div v-if="errorMessage" class="form-error-box">
      <div class="flex items-start">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
        </div>
        <div class="ml-3 flex-1">
          <h3 class="text-sm font-medium text-red-800">{{ t('common.error') }}</h3>
          <p class="mt-1 text-sm text-red-700">{{ errorMessage }}</p>
        </div>
        <button @click="errorMessage = ''" class="ml-3 flex-shrink-0 text-red-400 hover:text-red-600">
          <span class="sr-only">{{ t('common.dismiss') }}</span>
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>
      </div>
    </div>

    <form @submit.prevent="submitForm" class="space-y-6">
      <div class="section-card">
        <h2 class="text-lg font-semibold mb-4">{{ t('templates.basic_info') }}</h2>
        <div class="space-y-4">
          <div class="form-group">
            <label class="form-label">{{ t('templates.name') }}</label>
            <input v-model="form.name" maxlength="100" required class="form-input" :placeholder="t('templates.name_placeholder')" />
            <div class="form-hint">{{ t('templates.name_hint') }}</div>
          </div>
          <div class="form-group">
            <label class="form-label">{{ t('templates.type') }}</label>
            <select v-model="form.type" required class="form-select">
              <option value="">{{ t('templates.select_type') }}</option>
              <option value="post">Post</option>
              <option value="start">Start</option>
              <option value="category">Category</option>
              <option value="product">Product</option>
              <option value="images">Images</option>
            </select>
            <div class="form-hint">{{ t('templates.type_hint') }}</div>
          </div>
          <div v-if="form.type === 'images'" class="form-group">
            <label class="form-label">{{ t('templates.text') }}</label>
            <textarea v-model="form.text" class="form-input" rows="4" :placeholder="t('templates.text_placeholder')"></textarea>
            <div class="form-hint">{{ t('templates.text_hint') }}</div>
          </div>
        </div>
      </div>

      <div class="section-card">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold">{{ t('layout.button_layout') }}</h2>
          <button type="button" @click="addLine" class="btn btn-success btn-sm">
            {{ t('layout.add_line') }}
          </button>
        </div>

        <div v-if="form.layout.length === 0" class="empty-state py-8">
          <p>{{ t('layout.no_lines_template') }}</p>
        </div>

        <div v-else class="space-y-4">
          <div v-for="(line, lineIndex) in form.layout" :key="lineIndex" class="border border-gray-300 rounded-lg p-4 bg-gray-50">
            <div class="flex justify-between items-center mb-3">
              <h3 class="font-medium text-gray-700">{{ t('layout.line') }} {{ lineIndex + 1 }}</h3>
              <div class="flex gap-2">
                <button type="button" @click="addButtonToLine(lineIndex)" :disabled="line.length >= 8" class="btn btn-primary btn-sm">
                  {{ t('layout.add_button') }}
                </button>
                <button type="button" @click="removeLine(lineIndex)" class="btn btn-danger btn-sm">
                  {{ t('layout.remove_line') }}
                </button>
              </div>
            </div>

            <div v-if="line.length === 0" class="text-center py-4 text-gray-400 text-sm">
              {{ t('layout.no_buttons_in_line') }}
            </div>

            <div v-else class="space-y-2">
              <div v-for="buttonIndex in line.length" :key="buttonIndex" class="flex items-center gap-2 bg-white p-2 rounded border border-gray-200">
                <span class="text-sm font-medium text-gray-600 w-8">{{ buttonIndex }}.</span>
                <select v-model="form.layout[lineIndex][buttonIndex - 1]" required class="form-select flex-1 text-sm">
                  <option value="">{{ t('layout.select_button') }}</option>
                  <optgroup :label="t('layout.regular_buttons')">
                    <option v-for="button in availableButtons" :key="`button_${button.id}`" :value="`button_${button.id}`">
                      {{ button.label }} ({{ button.code }})
                    </option>
                  </optgroup>
                  <optgroup :label="t('templates.categories_label')">
                    <option v-for="category in allCategories" :key="`category_${category.id}`" :value="`category_${category.id}`">
                      {{ category.name }} (Category)
                    </option>
                  </optgroup>
                  <optgroup :label="t('templates.products_label')">
                    <option v-for="product in allProducts" :key="`product_${product.id}`" :value="`product_${product.id}`">
                      {{ product.name }} (Product)
                    </option>
                  </optgroup>
                </select>
                <button type="button" @click="moveButtonUp(lineIndex, buttonIndex - 1)" :disabled="buttonIndex === 1" class="btn btn-secondary btn-sm">↑</button>
                <button type="button" @click="moveButtonDown(lineIndex, buttonIndex - 1)" :disabled="buttonIndex === line.length" class="btn btn-secondary btn-sm">↓</button>
                <button type="button" @click="removeButton(lineIndex, buttonIndex - 1)" class="btn btn-danger btn-sm">{{ t('layout.remove_button') }}</button>
              </div>
              <div v-if="line.length >= 8" class="text-xs text-orange-600 mt-2">
                {{ t('layout.max_buttons') }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex gap-2 mt-4">
        <button type="submit" :disabled="isSubmitting || !isFormValid" class="btn btn-primary">
          {{ isSubmitting ? t('common.saving') : t('common.save') }}
        </button>
        <button type="button" @click="goBack" class="btn btn-secondary">{{ t('common.cancel') }}</button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import api from '../api';
import type { Button } from '../types/Button';

const { t } = useI18n();

interface Category { id: number; name: string; }
interface Product { id: number; name: string; }

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);

const form = ref<{
  name: string;
  type: string;
  text: string;
  layout: (string | number)[][];
}>({
  name: '',
  type: '',
  text: '',
  layout: [],
});

const availableButtons = ref<Button[]>([]);
const allCategories = ref<Category[]>([]);
const allProducts = ref<Product[]>([]);
const errorMessage = ref('');
const isSubmitting = ref(false);

const isFormValid = computed(() => {
  if (!form.value.name || !form.value.type) return false;
  if (form.value.layout.length === 0) return false;
  for (const line of form.value.layout) {
    if (line.length === 0) return false;
    for (const buttonId of line) {
      if (!buttonId || buttonId === 0 || buttonId === '') return false;
    }
  }
  return true;
});

const fetchButtons = async () => {
  try {
    const { data } = await api.get('/api/buttons');
    availableButtons.value = data['member'] || [];
  } catch (error: any) {
    errorMessage.value = t('common.error');
  }
};

const fetchCategories = async () => {
  try {
    const { data } = await api.get('/api/categories');
    allCategories.value = data['member'] || [];
  } catch (error: any) {
    console.error('Failed to load categories:', error);
  }
};

const fetchProducts = async () => {
  try {
    const { data } = await api.get('/api/products');
    allProducts.value = data['member'] || [];
  } catch (error: any) {
    console.error('Failed to load products:', error);
  }
};

const normalizeLayout = (layout: (string | number)[][]): (string | number)[][] => {
  return layout.map(line =>
    line.map(buttonId => {
      if (typeof buttonId === 'number' && buttonId > 0) return `button_${buttonId}`;
      return buttonId;
    })
  );
};

const fetchTemplate = async () => {
  if (isEdit.value) {
    try {
      const { data } = await api.get(`/api/templates/${route.params.id}`);
      form.value = {
        name: data.name,
        type: data.type,
        text: data.text || '',
        layout: normalizeLayout(data.layout || []),
      };
    } catch (error: any) {
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || t('common.error');
    }
  } else if (route.query.duplicateFrom) {
    try {
      const { data } = await api.get(`/api/templates/${route.query.duplicateFrom}`);
      form.value = {
        name: data.name + ' (Copy)',
        type: data.type,
        text: data.text || '',
        layout: normalizeLayout(data.layout || []),
      };
    } catch (error: any) {
      errorMessage.value = error.response?.data?.description || error.response?.data?.detail || t('common.error');
    }
  }
};

const addLine = () => { form.value.layout.push([]); };
const removeLine = (lineIndex: number) => { form.value.layout.splice(lineIndex, 1); };
const addButtonToLine = (lineIndex: number) => { if (form.value.layout[lineIndex].length < 8) form.value.layout[lineIndex].push(''); };
const removeButton = (lineIndex: number, buttonIndex: number) => { form.value.layout[lineIndex].splice(buttonIndex, 1); };
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
    errorMessage.value = apiError?.description || apiError?.detail || apiError?.title || t('common.error');
  } finally {
    isSubmitting.value = false;
  }
};

const goBack = () => { router.push({ name: 'TemplateList' }); };

onMounted(async () => {
  await fetchButtons();
  await fetchCategories();
  await fetchProducts();
  await fetchTemplate();
});
</script>
