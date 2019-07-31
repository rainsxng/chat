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
            Echo.join('chat')
                .listen('UserBanned',(event) => {
                    console.log('1');
                })
        },
        methods: {
          banUser() {
             if (!this.user.isBanned) {
                 axios.put('ban', { user: this.user } );
             }
             else {
                 console.log('banned');
             }
          }
        }
    }
</script>
