<template>
    <div class="row">
        <div class="col-10">

            <div v-if="users.length !== 0" class="card-body">
                <div class="card card-default mt-4">
                    Database users
                </div>
                <ul>
                        <li class="py-2" v-for="(user, index) in users" :key="index">
                            <span>{{ user.name }} <br> {{ user.email }} </span>
                             <mute-btn :user="user"></mute-btn>

                            <ban-btn :user="user"> </ban-btn>
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
