<template>
   <button class="btn btn-sm btn-outline-danger ml-2 mr-2" @click="banUser">
    <span v-if="this.user.isBanned">Unban</span>
       <span v-else>Ban</span>
       </button>
</template>

<script>
    export default {
        props: ['user'],
        methods: {
            banUser() {
                this.user.isBanned = !this.user.isBanned;
                axios.put('ban', {user: this.user})
            }
        }
    }
</script>
