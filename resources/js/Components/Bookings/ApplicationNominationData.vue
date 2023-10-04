<script setup>
let props = defineProps({ nominations: Object, appStatus: String, rejectReason: String, processedBy: String });

const pClass = "text-sm laptop:text-sm 1080:text-base 1440:text-lg 4k:text-xl";
</script>
<template>
<div>
    <div>
        <p :class="pClass" v-if="nominations != null">
            Nominations Accepted: {{ nominations && nominations.filter(n => n.status === 'Y').length }}/{{ nominations && nominations.length }}
        </p>
        <p v-if="appStatus === 'P' && nominations != null" :class="pClass">
            Nominations Undecided: {{ nominations && nominations.filter(n => n.status === 'U').length  }}
        </p>
        <p v-if="appStatus === 'P' && nominations != null" :class="pClass">
            Nominations Rejected: {{ nominations && nominations.filter(n => n.status === 'N').length  }}
        </p>
        <p v-if="appStatus === 'U'" :class="pClass">
            Awaiting Line Manager Decision
        </p>
        <p v-if="appStatus === 'N'" :class="pClass">
            Reason: {{ rejectReason }}
        </p>
        <p v-if="processedBy != null && appStatus !== 'U' && appStatus !== 'P' && appStatus !== 'C'" :class="pClass">
            Processed by: {{ processedBy }}
        </p>
    </div>
</div>
</template>