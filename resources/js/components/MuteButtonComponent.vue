<template>
    <button class="btn btn-sm btn-outline-danger">
        <span v-if="user.isMuted">Unmute</span>
        <span v-else>Mute</span>
    </button>
</template>

<script>
    export default {
        props:['user']
    }
</script>
