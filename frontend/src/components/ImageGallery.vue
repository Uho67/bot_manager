<template>
  <div>
    <label class="form-label">{{ t('products.additional_images') }}</label>

    <div v-if="images.length === 0" class="text-gray-400 text-sm mb-2">
      {{ t('products.no_images') }}
    </div>

    <div v-else class="grid grid-cols-3 gap-3 mb-3">
      <div
        v-for="(img, index) in images"
        :key="img.id"
        class="relative group border rounded overflow-hidden"
        draggable="true"
        @dragstart="onDragStart(index)"
        @dragover.prevent="onDragOver(index)"
        @drop="onDrop(index)"
      >
        <img
          :src="getImageUrl(img.image)"
          alt=""
          class="w-full h-24 object-cover"
        />
        <button
          type="button"
          @click="removeImage(img.id, index)"
          class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity"
          :title="t('products.remove_image')"
        >
          X
        </button>
        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs text-center py-0.5">
          {{ index + 1 }}
        </div>
      </div>
    </div>

    <div v-if="images.length >= maxImages" class="text-amber-600 text-sm mb-2">
      {{ t('products.max_images') }}
    </div>

    <div v-if="uploading" class="text-gray-500 text-sm mb-2">
      {{ t('common.loading') }}
    </div>

    <button
      v-if="images.length < maxImages"
      type="button"
      @click="triggerFileInput"
      :disabled="uploading"
      class="btn btn-secondary btn-sm"
    >
      {{ t('products.add_image') }}
    </button>

    <input
      ref="fileInput"
      type="file"
      accept="image/*"
      class="hidden"
      @change="onFileSelected"
    />

    <div v-if="errorMessage" class="text-red-600 text-sm mt-1">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '../api';
import type { ProductImage } from '../types/Product';

const props = defineProps<{
  images: ProductImage[];
  productId: number;
}>();

const emit = defineEmits<{
  'update:images': [value: ProductImage[]];
}>();

const { t } = useI18n();
const maxImages = 9;
const uploading = ref(false);
const errorMessage = ref('');
const fileInput = ref<HTMLInputElement | null>(null);
const dragIndex = ref<number | null>(null);

const getImageUrl = (path: string) => {
  if (path.startsWith('http')) return path;
  return `${import.meta.env.VITE_API_URL}${path}`;
};

const triggerFileInput = () => {
  fileInput.value?.click();
};

const onFileSelected = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (!target.files || target.files.length === 0) return;

  const file = target.files[0];
  errorMessage.value = '';
  uploading.value = true;

  try {
    const formData = new FormData();
    formData.append('image', file);

    const response = await api.post(
      `/api/product/${props.productId}/additional-images`,
      formData,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    );

    if (response.data.success) {
      const newImage: ProductImage = {
        id: response.data.id,
        image: response.data.path,
        sort_order: response.data.sort_order,
      };
      emit('update:images', [...props.images, newImage]);
    } else {
      errorMessage.value = t('products.failed_upload_additional');
    }
  } catch {
    errorMessage.value = t('products.failed_upload_additional');
  } finally {
    uploading.value = false;
    // Reset file input so the same file can be selected again
    if (fileInput.value) fileInput.value.value = '';
  }
};

const removeImage = async (imageId: number, index: number) => {
  errorMessage.value = '';
  try {
    await api.delete(`/api/product/additional-image/${imageId}`);
    const updated = [...props.images];
    updated.splice(index, 1);
    emit('update:images', updated);
  } catch {
    errorMessage.value = t('products.failed_delete_image');
  }
};

const onDragStart = (index: number) => {
  dragIndex.value = index;
};

const onDragOver = (_index: number) => {
  // Allow drop
};

const onDrop = async (dropIndex: number) => {
  if (dragIndex.value === null || dragIndex.value === dropIndex) return;

  const updated = [...props.images];
  const [moved] = updated.splice(dragIndex.value, 1);
  updated.splice(dropIndex, 0, moved);

  // Update sort_order based on new positions
  const reordered = updated.map((img, i) => ({ ...img, sort_order: i }));
  emit('update:images', reordered);

  dragIndex.value = null;

  // Persist reorder to backend
  try {
    await api.patch('/api/product/additional-images/reorder', {
      images: reordered.map((img) => ({ id: img.id, sort_order: img.sort_order })),
    });
  } catch {
    errorMessage.value = t('products.failed_reorder_images');
  }
};
</script>
