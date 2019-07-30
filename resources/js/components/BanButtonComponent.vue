<template>
   <button class="btn btn-sm btn-outline-danger">
    <span v-if="user.isBanned">Ban</span>
       <span v-else>Unban</span>
       </button>
</template>

<script>
    export default {
      props:['user']
    }
</script>
