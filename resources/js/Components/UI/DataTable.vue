<template>
  <div class="bg-surface rounded-xl border border-subtle overflow-hidden">
    <div class="p-4 border-b border-subtle flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <slot name="toolbar">
        <div v-if="searchable" class="relative">
          <SearchIcon class="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            v-model="searchTerm"
            type="text"
            :placeholder="searchPlaceholder"
            class="w-full pl-9 pr-3 py-1.5 text-sm border border-control rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-brand-primary"
            @input="onSearch"
          />
        </div>
      </slot>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-canvas border-b border-subtle text-xs text-muted uppercase tracking-wider sticky top-0">
          <tr>
            <th v-for="col in columns" :key="col.key" :class="col.class || 'px-4 py-3'">
              {{ col.label }}
            </th>
            <th v-if="$slots.actions" class="px-4 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-subtle">
          <tr v-if="loading" v-for="i in 5" :key="i">
            <td v-for="col in columns" :key="col.key" class="px-4 py-3">
              <div class="h-4 bg-slate-200 rounded animate-pulse" />
            </td>
            <td v-if="$slots.actions" class="px-4 py-3 text-right">
              <div class="h-4 w-12 bg-slate-200 rounded animate-pulse ml-auto" />
            </td>
          </tr>

          <tr v-else-if="rows.length === 0">
            <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-4 py-12 text-center text-muted">
              <div class="flex flex-col items-center justify-center">
                <slot name="empty-icon">
                  <InboxIcon class="w-10 h-10 text-muted/50 mb-2 stroke-[1.5]" />
                </slot>
                <div class="font-medium text-main mb-1">{{ emptyMessage }}</div>
                <slot name="empty-action" />
              </div>
            </td>
          </tr>

          <tr
            v-else
            v-for="(row, idx) in rows"
            :key="row.id ?? idx"
            class="hover:bg-canvas transition-colors odd:bg-white even:bg-slate-50/50"
          >
            <td v-for="col in columns" :key="col.key" :class="col.class || 'px-4 py-3 text-main'">
              <slot :name="col.key" :row="row" :value="row[col.key]">
                <span v-if="col.render">{{ col.render(row[col.key], row) }}</span>
                <span v-else>{{ row[col.key] ?? '-' }}</span>
              </slot>
            </td>
            <td v-if="$slots.actions" class="px-4 py-3 text-right">
              <slot name="actions" :row="row" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="pagination && pagination.links && pagination.links.length > 3" class="px-4 py-3 border-t border-subtle flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-muted">
      <div>
        Menampilkan {{ pagination.from || 0 }} - {{ pagination.to || 0 }} dari {{ pagination.total || 0 }} data
      </div>
      <div class="flex gap-1">
        <template v-for="(link, idx) in pagination.links" :key="idx">
          <Link
            v-if="link.url"
            :href="link.url"
            preserve-scroll
            preserve-state
            class="px-2.5 py-1 rounded border border-control hover:bg-canvas transition-colors"
            :class="{ 'bg-brand-primary text-white border-brand-primary font-semibold hover:bg-brand-primary': link.active }"
            v-html="link.label"
          />
          <span
            v-else
            class="px-2.5 py-1 rounded border border-transparent text-muted/50"
            v-html="link.label"
          />
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Search as SearchIcon, Inbox as InboxIcon } from 'lucide-vue-next';

export interface Column {
  key: string;
  label: string;
  class?: string;
  render?: (value: any, row: any) => string;
}

export interface Pagination {
  from?: number;
  to?: number;
  total?: number;
  links?: Array<{ url: string | null; label: string; active: boolean }>;
}

const props = withDefaults(defineProps<{
  columns: Column[];
  rows: any[];
  loading?: boolean;
  searchable?: boolean;
  searchPlaceholder?: string;
  emptyMessage?: string;
  pagination?: Pagination | null;
}>(), {
  loading: false,
  searchable: false,
  searchPlaceholder: 'Cari data...',
  emptyMessage: 'Belum ada data',
  pagination: null,
});

const emit = defineEmits<{
  search: [term: string];
}>();

const searchTerm = ref('');
let timeout: any = null;

const onSearch = () => {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    emit('search', searchTerm.value);
  }, 300);
};
</script>