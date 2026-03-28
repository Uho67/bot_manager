<template>
  <div>
    <div v-for="(config, index) in configs" :key="config.id || `new-${index}`" class="config-row">
      <label class="config-label">{{ config.name }}</label>
      <input
        v-model="editValues[getConfigKey(config, index)]"
        type="text"
        class="config-input"
        :placeholder="config.id ? '' : t('configs.enter_value')"
      />
      <button
        v-if="config.id"
        class="btn btn-primary btn-sm ml-2"
        @click="saveConfig(config, index)"
        :disabled="loading[getConfigKey(config, index)]"
      >
        <span v-if="loading[getConfigKey(config, index)]">{{ t('common.saving') }}</span>
        <span v-else>{{ t('common.save') }}</span>
      </button>
      <button
        v-else
        class="btn btn-success btn-sm ml-2"
        @click="addConfig(config, index)"
        :disabled="loading[getConfigKey(config, index)] || !editValues[getConfigKey(config, index)]"
      >
        <span v-if="loading[getConfigKey(config, index)]">{{ t('common.adding') }}</span>
        <span v-else>{{ t('common.add') }}</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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
