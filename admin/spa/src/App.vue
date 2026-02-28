<script setup>
import { computed, ref, watch } from 'vue'
import DateRangeFilters from './components/DateRangeFilters.vue'
import PaginationControls from './components/PaginationControls.vue'
import ReportsTable from './components/ReportsTable.vue'
import ReportTabs from './components/ReportTabs.vue'
import { useReportsQuery } from './composables/useReportsQuery'

const config = window.eltAdmin || {}
const activeView = ref('links')
const preset = ref('last30')
const to = ref(defaultToDate())
const from = ref(defaultFromDate(30))
const page = ref(1)
const perPage = ref(20)
const orderby = ref('click_count')
const order = ref('desc')

const PER_PAGE_OPTIONS = [10, 20, 50, 100]

watch(activeView, (view) => {
  page.value = 1
  orderby.value = view === 'links' ? 'click_count' : 'total_clicks'
  order.value = 'desc'
})

watch([from, to], () => {
  page.value = 1
})

watch(perPage, () => {
  page.value = 1
})

function formatDate(value) {
  return value.toISOString().slice(0, 10)
}

function defaultToDate() {
  const now = new Date()
  return formatDate(now)
}

function defaultFromDate(daysBack) {
  const value = new Date()
  value.setDate(value.getDate() - daysBack)
  return formatDate(value)
}

function daysForPreset(nextPreset) {
  if (nextPreset === 'last7') return 7
  if (nextPreset === 'last30') return 30
  if (nextPreset === 'last60') return 60
  if (nextPreset === 'last90') return 90
  if (nextPreset === 'last180') return 180
  return null
}

function applyPreset(nextPreset) {
  const days = daysForPreset(nextPreset)
  if (days === null) {
    preset.value = 'custom'
    return
  }

  const nextTo = defaultToDate()
  const nextFrom = defaultFromDate(days)
  preset.value = nextPreset
  from.value = nextFrom
  to.value = nextTo
}

function handleSort(key) {
  if (orderby.value === key) {
    order.value = order.value === 'desc' ? 'asc' : 'desc'
  } else {
    orderby.value = key
    order.value = 'desc'
  }
  page.value = 1
}

const { items, total, loading, error } = useReportsQuery({
  config,
  view: activeView,
  from,
  to,
  page,
  perPage,
  orderby,
  order,
})

const totalPages = computed(() => {
  if (!perPage.value || total.value === 0) return 1
  return Math.max(1, Math.ceil(total.value / perPage.value))
})

function updateFrom(value) {
  from.value = value
}

function updateTo(value) {
  to.value = value
}
</script>

<template>
  <div class="elt-spa">
    <ReportTabs v-model="activeView" />
    <DateRangeFilters
      :from="from"
      :to="to"
      :preset="preset"
      :max-date="defaultToDate()"
      @update:preset="applyPreset"
      @update:from="updateFrom"
      @update:to="updateTo"
    />

    <ReportsTable
      :view="activeView"
      :items="items"
      :loading="loading"
      :error="error"
      :orderby="orderby"
      :order="order"
      @sort="handleSort"
    />
    <PaginationControls
      :page="page"
      :total-pages="totalPages"
      :total="total"
      :per-page="perPage"
      :per-page-options="PER_PAGE_OPTIONS"
      :loading="loading"
      @update:page="page = $event"
      @update:per-page="(v) => { perPage = v }"
    />
  </div>
</template>
