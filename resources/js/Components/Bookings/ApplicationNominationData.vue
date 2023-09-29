<script setup>
let props = defineProps({ nominations: Object, appStatus: String, rejectReason: String, processedBy: String });

const pClass = "text-sm laptop:text-sm 1080:text-base 1440:text-lg 4k:text-xl";
</script>
<template>
<div>
    <div v-if="nominations != null">
        <p :class="pClass">
            Nominees Accepted: {{ nominations && nominations.filter(n => n.status === 'Y').length }}/{{ nominations && nominations.length }}
        </p>
        <p v-if="appStatus === 'P'" :class="pClass">
            Nominees Undecided: {{ nominations && nominations.filter(n => n.status === 'U').length  }}
        </p>
        <p v-if="appStatus === 'P'" :class="pClass">
            Nominees Rejected: {{ nominations && nominations.filter(n => n.status === 'N').length  }}
        </p>
        <p v-if="appStatus === 'U'" :class="pClass">
            Awaiting Line Manager Decision
        </p>
        <p v-if="appStatus === 'N'" :class="pClass">
            Reason: {{ rejectReason }}
        </p>
        <p v-if="processedBy != null && appStatus !== 'U' && appStatus !== 'P'" :class="pClass">
            Processed by: {{ processedBy }}
        </p>
    </div>
    <div v-if="nominations == null">
        <p v-if="appStatus === 'U'" :class="pClass">
            Awaiting Line Manager Decision
        </p>
    </div>
</div>
</template>