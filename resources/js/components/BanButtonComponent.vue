<template>
   <button class="btn btn-sm btn-outline-danger ml-2 mr-2" @click="banUser">
    <span v-if="user.isBanned">Unban</span>
       <span v-else>Ban</span>
       </button>
</template>

<script>
    export default {
      props:['user'],
        created() {

        },
        methods: {
          banUser() {
             if (!this.user.isBanned) {
                 this.user.isBanned = true;
                 axios.put('ban', { user: this.user } )
             }
             else {
                 this.user.isBanned = false;
                 axios.put('unban', { user: this.user } );
             }
          }
        }
    }
</script>
