<script setup>
import { computed } from 'vue'
import feather from 'feather-icons'

const props = defineProps({
  view: { type: String, required: true },
  items: { type: Array, required: true },
  loading: { type: Boolean, required: true },
  error: { type: String, default: '' },
  orderby: { type: String, default: '' },
  order: { type: String, default: 'desc' },
})

const emit = defineEmits(['sort'])

const linkColumns = [
  { label: 'Link URL', key: 'link_url' },
  { label: 'Anchor text', key: 'anchor_text' },
  { label: 'Source URL', key: 'source_url' },
  { label: 'Source post ID', key: 'source_post_id' },
  { label: 'Clicks', key: 'click_count' },
]
const domainColumns = [
  { label: 'Domain', key: 'domain' },
  { label: 'Total clicks', key: 'total_clicks' },
  { label: 'Unique links', key: 'unique_links' },
]

const columns = computed(() => (props.view === 'links' ? linkColumns : domainColumns))

function cellValue(value) {
  return value ?? '—'
}

function sortDirection(key) {
  if (props.orderby !== key) return null
  return props.order === 'asc' ? 'asc' : 'desc'
}

const arrowUp = feather.icons['arrow-up'].toSvg({ class: 'elt-sort-icon', width: '14', height: '14' })
const arrowDown = feather.icons['arrow-down'].toSvg({ class: 'elt-sort-icon', width: '14', height: '14' })

const loaderIcon = feather.icons.loader.toSvg({ class: 'elt-state-icon elt-spin', width: '18', height: '18' })
const alertIcon = feather.icons['alert-circle'].toSvg({ class: 'elt-state-icon', width: '18', height: '18' })
const emptyIcon = feather.icons.inbox.toSvg({ class: 'elt-state-icon', width: '18', height: '18' })
</script>

<template>
  <div v-if="loading && items.length === 0" class="elt-table-state is-loading">
    <span v-html="loaderIcon"></span>
    <span>Loading report data...</span>
  </div>

  <div v-else-if="error" class="elt-table-state is-error">
    <span v-html="alertIcon"></span>
    <span>{{ error }}</span>
  </div>

  <div v-else-if="items.length === 0" class="elt-table-state is-empty">
    <span v-html="emptyIcon"></span>
    <span>No clicks recorded in this date range.</span>
  </div>

  <div v-else>
    <div v-if="loading" class="elt-table-refreshing">
      <span v-html="loaderIcon"></span>
      <span>Refreshing data...</span>
    </div>

    <table class="widefat striped elt-table">
      <thead>
        <tr>
          <th
            v-for="col in columns"
            :key="col.key"
            class="elt-th-sortable"
            @click="emit('sort', col.key)"
          >
            <span class="elt-th-label">{{ col.label }}</span>
            <span v-if="sortDirection(col.key) === 'asc'" class="elt-th-sort" v-html="arrowUp"></span>
            <span v-else-if="sortDirection(col.key) === 'desc'" class="elt-th-sort" v-html="arrowDown"></span>
          </th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="(item, index) in items" :key="index">
          <template v-if="view === 'links'">
            <td><a :href="item.link_url" target="_blank" rel="noopener">{{ item.link_url }}</a></td>
            <td>{{ cellValue(item.anchor_text) }}</td>
            <td>{{ cellValue(item.source_url) }}</td>
            <td>{{ cellValue(item.source_post_id) }}</td>
            <td>{{ item.click_count }}</td>
          </template>
          <template v-else>
            <td>{{ cellValue(item.domain) }}</td>
            <td>{{ item.total_clicks }}</td>
            <td>{{ item.unique_links }}</td>
          </template>
        </tr>
      </tbody>
    </table>
  </div>
</template>
