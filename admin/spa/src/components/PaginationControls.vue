<script setup>
import { computed, ref, watch } from 'vue'
import feather from 'feather-icons'

const props = defineProps({
  page: { type: Number, required: true },
  totalPages: { type: Number, required: true },
  total: { type: Number, required: true },
  perPage: { type: Number, required: true },
  perPageOptions: { type: Array, default: () => [10, 20, 50, 100] },
  loading: { type: Boolean, required: true },
})

const emit = defineEmits(['update:page', 'update:perPage'])

const jumpPage = ref('')

watch(() => props.page, (p) => { jumpPage.value = String(p) }, { immediate: true })

const canFirst = computed(() => props.page > 1 && !props.loading)
const canPrev = computed(() => props.page > 1 && !props.loading)
const canNext = computed(() => props.page < props.totalPages && !props.loading)
const canLast = computed(() => props.page < props.totalPages && !props.loading)

const rangeStart = computed(() => props.total === 0 ? 0 : (props.page - 1) * props.perPage + 1)
const rangeEnd = computed(() => Math.min(props.page * props.perPage, props.total))

function goFirst() { if (canFirst.value) emit('update:page', 1) }
function goPrev() { if (canPrev.value) emit('update:page', props.page - 1) }
function goNext() { if (canNext.value) emit('update:page', props.page + 1) }
function goLast() { if (canLast.value) emit('update:page', props.totalPages) }

function onPerPageChange(ev) {
  const v = Number(ev.target.value)
  if (v > 0) emit('update:perPage', v)
}

function jumpToPage() {
  const num = parseInt(jumpPage.value, 10)
  if (Number.isNaN(num) || num < 1) return
  const target = Math.min(num, props.totalPages)
  emit('update:page', target)
  jumpPage.value = String(target)
}

const chevronsLeft = feather.icons['chevrons-left'].toSvg({ class: 'elt-pagination-icon', width: '16', height: '16' })
const chevronLeft = feather.icons['chevron-left'].toSvg({ class: 'elt-pagination-icon', width: '16', height: '16' })
const chevronRight = feather.icons['chevron-right'].toSvg({ class: 'elt-pagination-icon', width: '16', height: '16' })
const chevronsRight = feather.icons['chevrons-right'].toSvg({ class: 'elt-pagination-icon', width: '16', height: '16' })
</script>

<template>
  <div class="elt-pagination">
    <div class="elt-pagination-per-page">
      <label>
        Per page
        <select class="elt-pagination-select" :value="perPage" :disabled="loading" @change="onPerPageChange">
          <option v-for="n in perPageOptions" :key="n" :value="n">{{ n }}</option>
        </select>
      </label>
    </div>

    <div class="elt-pagination-range">
      {{ rangeStart }}–{{ rangeEnd }} of {{ total }}
    </div>

    <div class="elt-pagination-nav">
      <button type="button" :disabled="!canFirst" class="elt-pagination-btn" title="First" @click="goFirst">
        <span v-html="chevronsLeft"></span>
      </button>
      <button type="button" :disabled="!canPrev" class="elt-pagination-btn" title="Previous" @click="goPrev">
        <span v-html="chevronLeft"></span>
      </button>

      <span class="elt-pagination-jump">
        Page
        <input
          v-model="jumpPage"
          type="number"
          min="1"
          :max="totalPages"
          :disabled="loading"
          class="elt-pagination-input"
          @keydown.enter="jumpToPage"
        />
        of {{ totalPages }}
        <button type="button" class="elt-pagination-btn elt-pagination-go" :disabled="loading" @click="jumpToPage">Go</button>
      </span>

      <button type="button" :disabled="!canNext" class="elt-pagination-btn" title="Next" @click="goNext">
        <span v-html="chevronRight"></span>
      </button>
      <button type="button" :disabled="!canLast" class="elt-pagination-btn" title="Last" @click="goLast">
        <span v-html="chevronsRight"></span>
      </button>
    </div>
  </div>
</template>
