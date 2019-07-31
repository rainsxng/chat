<template>
    <button class="btn btn-sm btn-outline-danger ml-2 mr-2" @click="muteUser">
        <span v-if="user.isMuted">Unmute</span>
        <span v-else>Mute</span>
    </button>
</template>

<script>
    export default {
        props:['user'],
        methods: {
            muteUser() {
                    this.user.isMuted = !this.user.isMuted;
                  //  axios.put('mute', { user: this.user } )
            }
        }
    }
</script>
