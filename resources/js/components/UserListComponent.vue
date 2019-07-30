<template>
    <div class="row">
        <div class="col-10">

            <div v-if="users.length !== 0" class="card-body">
                <div class="card card-default mt-4">
                    Users
                </div>
                <ul>
                        <li class="py-2" v-for="(user, index) in users" :key="index">
                            <span>{{ user.name }} <br> {{ user.email }} </span>
                            <button class="btn btn-outline-danger btn-sm">
                                    <p v-if="user.isMuted">Unmute</p>
                                <p v-else>Mute</p>
                            </button>

                            <button class="btn btn-outline-danger btn-sm">
                                <p v-if="user.isBanned">Unblock</p>
                                <p v-else>Block</p>
                            </button>
                        </li>
                </ul>
            </div>
            <p v-else>There is no users in database</p>
        </div>
    </div>
</template>

<script>
    export default {
      props:['users']
    }
</script>
