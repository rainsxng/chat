<template>
    <button class="btn btn-sm btn-outline-danger ml-2 mr-2">
        <span v-if="user.isMuted">Unmute</span>
        <span v-else>Mute</span>
    </button>
</template>

<script>
    export default {
        props:['user']
    }
</script>
