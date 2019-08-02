<style scoped>
    .muted {
        color:red;
        font-size: 16px;
    }
    .admin {
        color:red;
    }
</style>

<template>
    <div class="row">
        <div class="col-6">
            <div class="card card-default" id="messageList">
                <div class="card-header p-2">Messages</div>
                <ul class="list-unstyled" style="height:300px; overflow-y:scroll" v-chat-scroll>
                    <li class="p-2" v-for="(message, index) in messages" :key="index">
                        <span> <img :src="'http://www.gravatar.com/avatar/' + message.user.gravatar_img + '?d=robohash&s=50'" alt=""></span>
                        <span class="admin" v-if="checkIsAdmin(message.user)"> ADMIN </span>
                        <strong class="mr-1" v-bind:style="{ color: message.user.color }" >{{ message.user.name }}  </strong> <b>:</b>
                        <span v-bind:style="{ color: message.user.color }">{{ message.message }}</span>

                    </li>
                </ul>
                <input v-if="!this.currentUser.isMuted" type="text" id="message" name="message" class="form-control p-2"
                       placeholder="Enter your message"
                       v-model="newMessage"
                       @keyup.enter="sendMessage"
                       @keydown="sendTypingEvent"
                       maxlength="200"
                       minlength="2"
                       required
                >
                <p class="ml-2 muted" v-else>You muted</p>


            </div>
            <span class=" ml-3 mt-3 mb-3 " v-bind:style="{ color: activeUser.color }" v-if="activeUser">{{activeUser.name}}</span> <span v-if="activeUser" class="text-muted"> is typing...</span>
        </div>
        <div class="col-6">
            <div class="card card-default">
                <span class="text-center">Online users</span>
            </div>
            <div class="card-body">
                <ul>
                        <li class="py-1" v-for="(user, index) in users" :key="index">
                            <span> <img :src="'http://www.gravatar.com/avatar/' + user.gravatar_img + '?d=robohash&s=50'" alt=""></span>
                            <span class="admin" v-if="checkIsAdmin(user)"> ADMIN </span>
                            <span v-bind:style="{ color: user.color }">{{ user.name }} </span> <br>
                            <span v-if="checkIsAdmin(currentUser)">{{ user.email }}
                            <mute-btn v-if="!checkIsAdmin(user)" :user="user"></mute-btn>
                            <ban-btn v-if="!checkIsAdmin(user)" :user="user"> </ban-btn>
                                </span>
                        </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
      props:['user'],
      data(){
          return {
              currentUser : [],
              messages: [],
              newMessage: '',
              users: [],
              activeUser: false,
              typingTimer: false,
              errorTimer: false
          }
      },
      created() {
            this.currentUser = this.user;
            this.fetchMessages();           //fetch all messages and users who wrote them from server

            Echo.join('chat')
                .here(user => {     //get all users , connected through websockets
                    this.users = user;
                })
                .joining(user => {
                    this.users.push(user);          //when user join, add him to online users list and refresh his message color
                    this.messages.forEach( ( message ) => {
                        if (message.user.id === user.id) {
                            message.user.color = user.color;
                        }
                    })
                })
                .leaving(user => {
                   this.users = this.users.filter(u => u.id !== user.id);   //when user leave chat , delete him from online users list
                })
                .listen('MessageSent',(event) => {      //when message delivered, push message with user who wrote it into chat
                    this.messages.push(event.message);
                })
                .listenForWhisper('typing', user => {
                    this.activeUser = user;     //show typing now hint
                    if(this.typingTimer) {
                        clearTimeout(this.typingTimer);
                    }
                    this.typingTimer = setTimeout(() => {
                        this.activeUser = false;
                    }, 1000);
                })
                .listen('UserBanned',(event) => {
                  if (this.user.id === event.user.id) {     //when user gets ban, disconnect him from websocket and reload page for banned user
                      Echo.disconnect();
                      location.reload();
                  }
              })
                .listen('UserMuted' , ( event ) => {
                    this.users.forEach((chatUser) => {
                        if (chatUser.id === event.user.id) {      //when user get mute , change status ismuted in vue component for buttons
                            chatUser.isMuted = event.user.isMuted;
                        }
                    });
                    if (this.user.id === event.user.id) {       //when user gets mute, change status ismuted for current user
                        this.currentUser.isMuted = event.user.isMuted;
                    }
                })
      },
        methods: {
          fetchMessages() {
                // Get all messages and information about users who wrote it
                 axios.get('/messages').then(response => {
                    this.messages = response.data;
            })
        },
            checkIsAdmin ( user ) {
               return user.role === 'admin';
            },
            showErrorMessage(message) {
                //Generate error message for 2 second
                if ($('#error').length) {
                    $('#error').remove();       //First, remove error message , if it already exists
                }
                $('#messageList').append(`<div id="error" class="alert alert-warning alert-dismissible fade show" role="alert">
                 <strong>${message}</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                </div>`); //Create new one after chat
                if(this.errorTimer) {
                    clearTimeout(this.errorTimer);
                }       //Delete error message after 2 sec
                this.errorTimer = setTimeout ( () => {
                    $('#error').alert('close')
                }, 2000)
            },
            sendMessage() {
                if ($('#message').val().length <= 1) {
                    this.showErrorMessage('Message field should be required, and be at least 2 letters long'); //Check if message field is not empty
                }
                else {
                    axios.post('messages', {message: this.newMessage})
                        .then(() => {
                            this.messages.push({
                                user: this.user,
                                message: this.newMessage            // Send post request to server, then push message to chat
                            });
                            this.newMessage = '';
                            this.activeUser = false;
                        })
                        .catch((error) => {
                           this.showErrorMessage(error.response.data)   //If there is some error, show user-friendly text in error message
                        });

                     $('#message').prop("disabled", true);
                     setTimeout(() => {         //disable input field for 15 seconds
                         $('#message').prop("disabled", false);
                     }, 15000)
                }
            },
            sendTypingEvent () {
                Echo.join('chat')
                    .whisper('typing', this.user);      // send client event when user start typing
            }
      }
    }
</script>
