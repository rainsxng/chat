<template>
    <div class="row">
        <div class="col-10">

            <div v-if="dbUsers.length !== 0" class="card-body">
                <div class="card card-default mt-4">
                    Database users
                </div>
                <ul>
                    <li class="py-2" v-for="(user, index) in dbUsers" :key="index">
                        <span> <img :src="'http://www.gravatar.com/avatar/' + user.gravatar_img + '?d=robohash&s=50'" alt=""></span>
                        <span v-bind:style="{ color: user.color }">{{ user.name }}</span> <br> <span>{{ user.email }} </span>
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
        props:['users'],
        created () {
            this.dbUsers = this.users;
            Echo.join('chat')
                .listen('UserMuted' , ( event ) => {
                    this.dbUsers.forEach((dbUser) => {
                        if (dbUser.id === event.user.id) {   //when user gets mute, change isMuted status user in a list
                            dbUser.isMuted = event.user.isMuted;
                        }
                    })
                })
                .listen('UserBanned' , ( event ) => {
                    this.dbUsers.forEach((dbUser) => {
                        if (dbUser.id === event.user.id) {
                            dbUser.isBanned = event.user.isBanned;
                        }
                    })  //when user gets ban, change isBanned status user in a list
                })
                .listen('UserRegistered', ( event ) => {
                    this.dbUsers.push(event.user);      //when user register, add him for "all users" list , which available only to admin
                })

        },
        data() {
            return {
                dbUsers: []
            }
        }
    }
</script>
