<template>
  <div class="flex items-center justify-end gap-1.5">
    <!-- Workload Report Actions -->
    <template v-if="itemType === 'workload'">
      <!-- Diajuakan / Divalidasi - can approve/reject/cancel -->
      <template v-if="isPendingOrValidated">
        <AppButton
          variant="primary"
          size="sm"
          :disabled="!props.isAdminOrHead"
          @click="handleApprove"
        >
          Setujui
        </AppButton>
        <AppButton
          variant="danger"
          size="sm"
          :disabled="!props.isAdminOrHead"
          @click="handleReject"
        >
          Tolak
        </AppButton>
        <AppButton
          variant="secondary"
          size="sm"
          @click="handleCancel"
        >
          Batalkan
        </AppButton>
      </template>

      <!-- Disetujui - can revise -->
      <template v-else-if="item.status === 'disetujui'">
        <AppButton
          variant="secondary"
          size="sm"
          :disabled="!props.isAdminOrHead"
          @click="handleRevise"
        >
          Revisi
        </AppButton>
      </template>

      <!-- Ditolak / Dibatalkan / Final - no actions -->
    </template>

    <!-- SKP Actions -->
    <template v-else-if="itemType === 'skp'">
      <!-- Diajuakan - can evaluate/reject/cancel -->
      <template v-if="item.status === 'diajukan'">
        <AppButton
          variant="primary"
          size="sm"
          :disabled="!props.isAdminOrHead"
          @click="handleEvaluate"
        >
          Nilai
        </AppButton>
        <AppButton
          variant="danger"
          size="sm"
          :disabled="!props.isAdminOrHead"
          @click="handleReject"
        >
          Tolak
        </AppButton>
        <AppButton
          variant="secondary"
          size="sm"
          @click="handleCancel"
        >
          Batalkan
        </AppButton>
      </template>

      <!-- Dinilai - can approve/reject -->
      <template v-else-if="item.status === 'dinilai'">
        <AppButton
          variant="primary"
          size="sm"
          :disabled="!props.isAdminOrHead"
          @click="handleApprove"
        >
          Setujui
        </AppButton>
        <AppButton
          variant="danger"
          size="sm"
          :disabled="!props.isAdminOrHead"
          @click="handleReject"
        >
          Tolak
        </AppButton>
      </template>
    </template>

    <!-- Academic Rank Actions -->
    <template v-else-if="itemType === 'academic'">
      <!-- Diajuakan / Divalidasi - can approve/reject/cancel -->
      <template v-if="isPendingOrValidated">
        <AppButton
          variant="primary"
          size="sm"
          :disabled="!props.isAdminOrHead"
          @click="handleApprove"
        >
          Setujui
        </AppButton>
        <AppButton
          variant="danger"
          size="sm"
          :disabled="!props.isAdminOrHead"
          @click="handleReject"
        >
          Tolak
        </AppButton>
        <AppButton
          variant="secondary"
          size="sm"
          @click="handleCancel"
        >
          Batalkan
        </AppButton>
      </template>
    </template>

    <!-- Staff Rank Actions -->
    <template v-else-if="itemType === 'staff'">
      <!-- Diajuakan / Divalidasi - can approve/reject/cancel -->
      <template v-if="isPendingOrValidated">
        <AppButton
          variant="primary"
          size="sm"
          :disabled="!props.isAdminOrHead"
          @click="handleApprove"
        >
          Setujui
        </AppButton>
        <AppButton
          variant="danger"
          size="sm"
          :disabled="!props.isAdminOrHead"
          @click="handleReject"
        >
          Tolak
        </AppButton>
        <AppButton
          variant="secondary"
          size="sm"
          @click="handleCancel"
        >
          Batalkan
        </AppButton>
      </template>
    </template>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import AppButton from '../../../Components/UI/AppButton.vue'
import { router } from '@inertiajs/vue3'

interface Props {
  item: any
  isAdminOrHead: boolean
  itemType: 'workload' | 'skp' | 'academic' | 'staff'
}

const props = defineProps<Props>()

const emit = defineEmits<{
  reload: []
  'open-reason-modal': [action: any]
  conflict: [version: number]
}>()

const isPendingOrValidated = computed(() => {
  return ['diajukan', 'divalidasi'].includes(props.item.status?.toLowerCase() || '')
})

const getApproveRoute = () => {
  if (props.itemType === 'workload') {
    return route('performance.workload.validate', props.item.id)
  } else if (props.itemType === 'skp') {
    return route('performance.skp.evaluate', props.item.id)
  } else if (props.itemType === 'academic') {
    return route('career.academic.approve', props.item.id)
  } else if (props.itemType === 'staff') {
    return route('career.staff.approve', props.item.id)
  }
}

const getRejectRoute = () => {
  if (props.itemType === 'workload') {
    // Workload uses revise for 'reject' - actually reject not implemented, but UI has reject
    // For workload, reject action is not in API, but let's map to revise or just leave as placeholder
    return route('performance.workload.revise', props.item.id)
  } else if (props.itemType === 'skp') {
    return route('performance.skp.reject', props.item.id)
  } else if (props.itemType === 'academic') {
    return route('career.academic.reject', props.item.id)
  } else if (props.itemType === 'staff') {
    return route('career.staff.reject', props.item.id)
  }
}

const getCancelRoute = () => {
  // Cancel for workload not in API, but we can use revise or just placeholder
  // For now, for workload there's no cancel endpoint, but UI shows cancel button
  // For other types, we don't have cancel endpoints either
  // Return empty for now - will use revise for workload
  if (props.itemType === 'workload') {
    return route('performance.workload.revise', props.item.id)
  }
  return ''
}

const getReviseRoute = () => {
  if (props.itemType === 'workload') {
    return route('performance.workload.revise', props.item.id)
  }
  return ''
}

const handleApprove = () => {
  const routeUrl = getApproveRoute()
  router.post(routeUrl, {}, {
    preserveState: true,
    onSuccess: () => emit('reload'),
    onError: (errors) => {
      if (errors.status === 409) {
        emit('conflict', props.item.version)
      }
    },
  })
}

const handleReject = () => {
  const routeUrl = getRejectRoute()
  emit('open-reason-modal', {
    type: 'reject',
    item: props.item,
    itemType: props.itemType,
    endpoint: routeUrl,
    version: props.item.version,
  })
}

const handleCancel = () => {
  const routeUrl = getCancelRoute()
  emit('open-reason-modal', {
    type: 'cancel',
    item: props.item,
    itemType: props.itemType,
    endpoint: routeUrl,
    version: props.item.version,
  })
}

const handleRevise = () => {
  const routeUrl = getReviseRoute()
  emit('open-reason-modal', {
    type: 'revise',
    item: props.item,
    itemType: props.itemType,
    endpoint: routeUrl,
    version: props.item.version,
  })
}

const handleEvaluate = () => {
  // For SKP evaluate, we might need a separate flow
  const routeUrl = getApproveRoute() // skp evaluate route
  router.post(routeUrl, {}, {
    preserveState: true,
    onSuccess: () => emit('reload'),
    onError: (errors) => {
      if (errors.status === 409) {
        emit('conflict', props.item.version)
      }
    },
  })
}
</script>