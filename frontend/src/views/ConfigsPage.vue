<template>
  <div class="p-4 max-w-2xl mx-auto">
    <h1 class="text-xl font-bold mb-4">Configs</h1>
    <div v-if="isSuperAdmin">
      <div v-for="(configs, botId) in groupedConfigs" :key="botId" class="mb-8 border rounded p-4">
        <h2 class="font-semibold mb-2">Bot: {{ botId }}</h2>
        <ConfigList :configs="configs" @update="onUpdate" @add="onAdd" />
      </div>
    </div>
    <div v-else>
      <ConfigList :configs="mergedConfigs" @update="onUpdate" @add="onAdd" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useAuth } from '../composables/useAuth';
import ConfigList from '../components/ConfigList.vue';

interface ConfigItem {
  id?: number;
  name: string;
  value: string;
  path: string;
  bot_identifier?: string;
}

interface SchemaItem {
  name: string;
  path: string;
  type: string;
  default: string;
}

const configsData = ref<ConfigItem[]>([]);
const schemaData = ref<SchemaItem[]>([]);
const userStore = useAuth();
const isSuperAdmin = computed(() => userStore.user.value?.roles?.includes('ROLE_SUPER_ADMIN'));
const userBotIdentifier = computed(() => userStore.user.value?.bot_identifier || '');

const groupedConfigs = computed(() => {
  const grouped: Record<string, ConfigItem[]> = {};
  configsData.value.forEach((config) => {
    if (!grouped[config.bot_identifier!]) {
      grouped[config.bot_identifier!] = [];
    }
    grouped[config.bot_identifier!].push(config);
  });
  return grouped;
});

const mergedConfigs = computed(() => {
  const existingPaths = new Set(configsData.value.map(c => c.path));
  const merged: ConfigItem[] = [...configsData.value];

  // Add schema items that don't exist yet
  schemaData.value.forEach(schema => {
    if (!existingPaths.has(schema.path)) {
      merged.push({
        name: schema.name,
        path: schema.path,
        value: schema.default,
        bot_identifier: userBotIdentifier.value,
      });
    }
  });

  return merged;
});

const fetchSchema = async () => {
  const { data } = await axios.get(`${import.meta.env.VITE_API_URL}api/config/schema`, {
    headers: { Authorization: `Bearer ${userStore.token.value}` },
  });
  schemaData.value = data;
};

const fetchConfigs = async () => {
  const { data } = await axios.get(`${import.meta.env.VITE_API_URL}api/configs`, {
    headers: { Authorization: `Bearer ${userStore.token.value}` },
  });
  configsData.value = data;
};

const onUpdate = async (id: number, value: string) => {
  await axios.patch(`${import.meta.env.VITE_API_URL}api/configs/${id}`, { value }, {
    headers: { Authorization: `Bearer ${userStore.token.value}` },
  });
  await fetchConfigs();
};

const onAdd = async (path: string, value: string, name: string) => {
  await axios.post(`${import.meta.env.VITE_API_URL}api/configs`, {
    path,
    value,
    name,
    bot_identifier: userBotIdentifier.value,
  }, {
    headers: { Authorization: `Bearer ${userStore.token.value}` },
  });
  await fetchConfigs();
};

onMounted(async () => {
  await fetchSchema();
  await fetchConfigs();
});
</script>
