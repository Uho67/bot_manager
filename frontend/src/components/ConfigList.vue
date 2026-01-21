<template>
  <div>
    <div v-for="(config, index) in configs" :key="config.id || `new-${index}`" class="mb-4 flex items-center gap-2">
      <label class="w-48 font-medium">{{ config.name }}</label>
      <input
        v-model="editValues[getConfigKey(config, index)]"
        type="text"
        class="border rounded px-2 py-1 flex-1"
        :placeholder="config.id ? '' : 'Enter value'"
      />
      <button
        v-if="config.id"
        class="ml-2 px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
        @click="saveConfig(config, index)"
        :disabled="loading[getConfigKey(config, index)]"
      >
        <span v-if="loading[getConfigKey(config, index)]">Saving...</span>
        <span v-else>Save</span>
      </button>
      <button
        v-else
        class="ml-2 px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
        @click="addConfig(config, index)"
        :disabled="loading[getConfigKey(config, index)] || !editValues[getConfigKey(config, index)]"
      >
        <span v-if="loading[getConfigKey(config, index)]">Adding...</span>
        <span v-else>Add</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

interface ConfigItem {
  id?: number;
  name: string;
  value: string;
  path: string;
  bot_identifier?: string;
}

const props = defineProps<{ configs: ConfigItem[] }>();
const emit = defineEmits(['update', 'add']);

const editValues = ref<{ [key: string]: string }>({});
const loading = ref<{ [key: string]: boolean }>({});

const getConfigKey = (config: ConfigItem, index: number): string => {
  return config.id ? `id-${config.id}` : `new-${index}`;
};

watch(() => props.configs, (newConfigs) => {
  newConfigs.forEach((config, index) => {
    const key = getConfigKey(config, index);
    editValues.value[key] = config.value || '';
  });
}, { immediate: true });

const saveConfig = async (config: ConfigItem, index: number) => {
  const key = getConfigKey(config, index);
  loading.value[key] = true;
  await emit('update', config.id!, editValues.value[key]);
  loading.value[key] = false;
};

const addConfig = async (config: ConfigItem, index: number) => {
  const key = getConfigKey(config, index);
  loading.value[key] = true;
  await emit('add', config.path, editValues.value[key], config.name);
  loading.value[key] = false;
};
</script>
