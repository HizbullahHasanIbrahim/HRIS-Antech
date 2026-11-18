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

const handleApprove = () => {
  if (props.itemType === 'workload') {
    router.post(`/api/v1/workload-reports/${props.item.id}/validate`, {}, {
      preserveState: true,
      onSuccess: () => emit('reload'),
      onError: (errors) => {
        if (errors.status === 409) {
          emit('conflict', props.item.version)
        }
      },
    })
  } else if (props.itemType === 'skp') {
    router.post(`/api/v1/skp/targets/${props.item.id}/evaluate`, {}, {
      preserveState: true,
      onSuccess: () => emit('reload'),
      onError: (errors) => {
        if (errors.status === 409) {
          emit('conflict', props.item.version)
        }
      },
    })
  } else if (props.itemType === 'academic') {
    router.post(`/api/v1/academic-ranks/${props.item.id}`, {}, {
      preserveState: true,
      onSuccess: () => emit('reload'),
      onError: (errors) => {
        if (errors.status === 409) {
          emit('conflict', props.item.version)
        }
      },
    })
  } else if (props.itemType === 'staff') {
    router.post(`/api/v1/staff-ranks/${props.item.id}`, {}, {
      preserveState: true,
      onSuccess: () => emit('reload'),
      onError: (errors) => {
        if (errors.status === 409) {
          emit('conflict', props.item.version)
        }
      },
    })
  }
}

const handleReject = () => {
  if (props.itemType === 'workload') {
    emit('open-reason-modal', {
      type: 'reject',
      item: props.item,
      itemType: props.itemType,
      endpoint: `/api/v1/workload-reports/${props.item.id}/reject`,
      version: props.item.version,
    })
  } else if (props.itemType === 'skp') {
    emit('open-reason-modal', {
      type: 'reject',
      item: props.item,
      itemType: props.itemType,
      endpoint: `/api/v1/skp/targets/${props.item.id}/reject`,
      version: props.item.version,
    })
  } else if (props.itemType === 'academic') {
    emit('open-reason-modal', {
      type: 'reject',
      item: props.item,
      itemType: props.itemType,
      endpoint: `/api/v1/academic-ranks/${props.item.id}/reject`,
      version: props.item.version,
    })
  } else if (props.itemType === 'staff') {
    emit('open-reason-modal', {
      type: 'reject',
      item: props.item,
      itemType: props.itemType,
      endpoint: `/api/v1/staff-ranks/${props.item.id}/reject`,
      version: props.item.version,
    })
  }
}

const handleCancel = () => {
  if (props.itemType === 'workload') {
    emit('open-reason-modal', {
      type: 'cancel',
      item: props.item,
      itemType: props.itemType,
      endpoint: `/api/v1/workload-reports/${props.item.id}/cancel`,
      version: props.item.version,
    })
  } else if (props.itemType === 'skp') {
    emit('open-reason-modal', {
      type: 'cancel',
      item: props.item,
      itemType: props.itemType,
      endpoint: `/api/v1/skp/targets/${props.item.id}/cancel`,
      version: props.item.version,
    })
  } else if (props.itemType === 'academic') {
    emit('open-reason-modal', {
      type: 'cancel',
      item: props.item,
      itemType: props.itemType,
      endpoint: `/api/v1/academic-ranks/${props.item.id}/cancel`,
      version: props.item.version,
    })
  } else if (props.itemType === 'staff') {
    emit('open-reason-modal', {
      type: 'cancel',
      item: props.item,
      itemType: props.itemType,
      endpoint: `/api/v1/staff-ranks/${props.item.id}/cancel`,
      version: props.item.version,
    })
  }
}

const handleRevise = () => {
  if (props.itemType === 'workload') {
    emit('open-reason-modal', {
      type: 'revise',
      item: props.item,
      itemType: props.itemType,
      endpoint: `/api/v1/workload-reports/${props.item.id}/revise`,
      version: props.item.version,
    })
  }
}

const handleEvaluate = () => {
  // For SKP evaluate, we might need a separate flow
  router.post(`/api/v1/skp/targets/${props.item.id}/evaluate`, {}, {
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