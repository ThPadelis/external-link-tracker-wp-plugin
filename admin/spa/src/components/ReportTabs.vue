<script setup>
defineProps({
  modelValue: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div class="elt-tabs">
    <button
      type="button"
      class="elt-tab"
      :class="{ 'is-active': modelValue === 'links' }"
      @click="emit('update:modelValue', 'links')"
    >
      Link View
    </button>
    <button
      type="button"
      class="elt-tab"
      :class="{ 'is-active': modelValue === 'domains' }"
      @click="emit('update:modelValue', 'domains')"
    >
      Domain View
    </button>
  </div>
</template>
