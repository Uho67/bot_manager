<template>
  <div>
    <div class="rich-toolbar">
      <button
        type="button"
        @click="wrapBold"
        class="rich-toolbar-btn"
        title="Bold"
      >B</button>
    </div>
    <textarea
      ref="textareaRef"
      :value="modelValue"
      :rows="rows"
      :required="required"
      :placeholder="placeholder"
      class="form-textarea"
      @input="onInput"
    ></textarea>
    <div class="rich-hint">Use &lt;b&gt;word&lt;/b&gt; for bold text</div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const props = withDefaults(defineProps<{
  modelValue: string;
  rows?: number;
  required?: boolean;
  placeholder?: string;
}>(), {
  rows: 5,
  required: false,
  placeholder: '',
});

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();

const textareaRef = ref<HTMLTextAreaElement | null>(null);

const onInput = (event: Event) => {
  emit('update:modelValue', (event.target as HTMLTextAreaElement).value);
};

const wrapBold = () => {
  const el = textareaRef.value;
  if (!el) return;

  const start = el.selectionStart;
  const end = el.selectionEnd;
  const value = props.modelValue;
  const selected = value.slice(start, end);

  let newValue: string;
  let cursorPos: number;

  if (selected.length > 0) {
    newValue = value.slice(0, start) + '<b>' + selected + '</b>' + value.slice(end);
    cursorPos = end + 7; // after </b>
  } else {
    newValue = value.slice(0, start) + '<b></b>' + value.slice(start);
    cursorPos = start + 3; // between <b> and </b>
  }

  emit('update:modelValue', newValue);

  // Restore cursor after Vue re-renders
  setTimeout(() => {
    el.setSelectionRange(cursorPos, cursorPos);
    el.focus();
  }, 0);
};
</script>


