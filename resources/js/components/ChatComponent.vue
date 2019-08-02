<style scoped>
    .muted {
        color:red;
        font-size: 16px;
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
                            <span v-bind:style="{ color: user.color }">{{ user.name }} </span> <br>
                            <span v-if="checkIsAdmin()">{{ user.email }}
                            <mute-btn v-if=" user.role !== 'admin' " :user="user"></mute-btn>
                            <ban-btn v-if=" user.role !== 'admin' " :user="user"> </ban-btn>
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
            this.fetchMessages();

            Echo.join('chat')
                .here(user => {
                    this.users = user;
                })
                .joining(user => {
                    this.users.push(user);
                    this.messages.forEach( ( message ) => {
                        if (message.user.id === user.id) {
                            message.user.color = user.color;
                        }
                    })
                })
                .leaving(user => {
                   this.users = this.users.filter(u => u.id !== user.id);
                })
                .listen('MessageSent',(event) => {
                    this.messages.push(event.message);
                })
                .listenForWhisper('typing', user => {
                    this.activeUser = user;
                    if(this.typingTimer) {
                        clearTimeout(this.typingTimer);
                    }
                    this.typingTimer = setTimeout(() => {
                        this.activeUser = false;
                    }, 1000);
                })
                .listen('UserBanned',(event) => {
                  if (this.user.id === event.user.id) {
                      Echo.disconnect();
                      location.reload();
                  }
              })
                .listen('UserMuted' , ( event ) => {
                    this.users.forEach((chatUser) => {
                        if (chatUser.id === event.user.id) {
                            chatUser.isMuted = event.user.isMuted;
                        }
                    });
                    if (this.user.id === event.user.id) {
                        this.currentUser.isMuted = event.user.isMuted;
                    }
                })
      },
        methods: {
          fetchMessages() {
                axios.get('/messages').then(response => {
                    this.messages = response.data;
            })
        },
            checkIsAdmin (  ) {
               return this.user.role === 'admin';
            },
            showErrorMessage(message) {
                if ($('#error').length) {
                    $('#error').remove();
                }
                $('#messageList').append(`<div id="error" class="alert alert-warning alert-dismissible fade show" role="alert">
                 <strong>${message}</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                </div>`);
                if(this.errorTimer) {
                    clearTimeout(this.errorTimer);
                }
                this.errorTimer = setTimeout ( () => {
                    $('#error').alert('close')
                }, 2000)
            },
            sendMessage() {
                if ($('#message').val().length <= 1) {
                    this.showErrorMessage('Message field should be required, and be at least 2 letters long');
                }
                else {
                    axios.post('messages', {message: this.newMessage})
                        .then(() => {
                            this.messages.push({
                                user: this.user,
                                message: this.newMessage
                            });
                            this.newMessage = '';
                            this.activeUser = false;
                        })
                        .catch((error) => {
                           this.showErrorMessage(error.response.data)
                        });

                     $('#message').prop("disabled", true);
                     setTimeout(() => {
                         $('#message').prop("disabled", false);
                     }, 15000)
                }
            },
            sendTypingEvent () {
                Echo.join('chat')
                    .whisper('typing', this.user);
            }
      }
    }
</script>
