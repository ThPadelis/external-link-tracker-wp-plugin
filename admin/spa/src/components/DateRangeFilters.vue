<script setup>
import { computed } from 'vue'

const props = defineProps({
  from: {
    type: String,
    required: true,
  },
  to: {
    type: String,
    required: true,
  },
  preset: {
    type: String,
    required: true,
  },
  maxDate: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['update:from', 'update:to', 'update:preset'])

const fromMax = computed(() => (props.to < props.maxDate ? props.to : props.maxDate))
const toMin = computed(() => props.from)

function onPresetChange(value) {
  emit('update:preset', value)
}

function onFromInput(value) {
  const nextFrom = value > props.maxDate ? props.maxDate : value
  emit('update:preset', 'custom')
  emit('update:from', nextFrom)

  if (nextFrom > props.to) {
    emit('update:to', nextFrom)
  }
}

function onToInput(value) {
  const nextTo = value > props.maxDate ? props.maxDate : value
  emit('update:preset', 'custom')
  emit('update:to', nextTo)

  if (nextTo < props.from) {
    emit('update:from', nextTo)
  }
}
</script>

<template>
  <div class="elt-filters">
    <label>
      Date range
      <select :value="preset" @change="onPresetChange($event.target.value)">
        <option value="last7">Last week</option>
        <option value="last30">Last month</option>
        <option value="last60">Last two months</option>
        <option value="last90">Last three months</option>
        <option value="last180">Last six months</option>
        <option value="custom">Custom</option>
      </select>
    </label>
    <label>
      From
      <input :value="from" :max="fromMax" type="date" @input="onFromInput($event.target.value)" />
    </label>
    <label>
      To
      <input :value="to" :min="toMin" :max="maxDate" type="date" @input="onToInput($event.target.value)" />
    </label>
  </div>
</template>
