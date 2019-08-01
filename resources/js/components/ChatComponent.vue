<style scoped>
    .muted {
        color:red;
        font-size: 16px;
    }
</style>

<template>
    <div class="row">
        <div class="col-6">
            <div class="card card-default">
                <div class="card-header p-2">Messages</div>
                <ul class="list-unstyled" style="height:300px; overflow-y:scroll" v-chat-scroll>
                    <li class="p-2" v-for="(message, index) in messages" :key="index">
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
                >
                <p class="ml-2 muted" v-else>You muted</p>


            </div>
            <span class="text-muted m-3" v-if="activeUser">{{activeUser.name}} is typing...</span>
        </div>
        <div class="col-6">
            <div class="card card-default">
                <span class="text-center">Online users</span>
            </div>
            <div class="card-body">
                <ul>
                        <li class="py-1" v-for="(user, index) in users" :key="index">
                            <span v-bind:style="{ color: user.color }">{{ user.name }} </span> <br>
                            <span v-if="checkIsAdmin()">{{ user.email }}</span>
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
              typingTimer: false
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
                .listen('UserMuted', ( event ) => {
                    if (this.user.id === event.user.id) {
                        this.currentUser.isMuted = !this.currentUser.isMuted;
                    }
                })
      },
        methods: {
        fetchMessages() {
                axios.get('/messages').then(response => {
                    this.messages = response.data;
            })
        },
            checkIsAdmin ( ) {
               return this.user.role === 'admin';
            },
            sendMessage() {
                this.messages.push({
                    user: this.user,
                    message: this.newMessage
                });
                axios.post('messages', {message: this.newMessage});
                this.newMessage='';
                this.activeUser=false;
                $('#message').prop("disabled", true);
                setTimeout(() => {
                    $('#message').prop("disabled", false);
                }, 15000)
            },
            sendTypingEvent () {
                Echo.join('chat')
                    .whisper('typing', this.user);
            }
      }
    }
</script>
