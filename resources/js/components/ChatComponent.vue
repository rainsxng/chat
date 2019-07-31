<template>
    <div class="row">
        <div class="col-6">
            <div class="card card-default">
                <div class="card-header p-2">Messages</div>
                <ul class="list-unstyled" style="height:300px; overflow-y:scroll" v-chat-scroll>
                    <li class="p-2" v-for="(message, index) in messages" :key="index">
                        <strong>{{ message.user.name }}</strong>
                        <span>{{ message.message }}</span>
                    </li>
                </ul>
                <input v-if="!this.user.isMuted" type="text" id="message" name="message" class="form-control p-2"
                       placeholder="Enter your message"
                       v-model="newMessage"
                       @keyup.enter="sendMessage"
                       @keydown="sendTypingEvent"
                       maxlength="200"
                >
                <p class="ml-2" v-else>You muted</p>


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
                            <span>{{ user.name }} </span> <br>
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
              messages: [],
              newMessage: '',
              users: [],
              activeUser: false,
              typingTimer: false
          }
      },
      created() {
            this.fetchMessages();

            Echo.join('chat')
                .here(user => {
                    this.users = user.filter(u => u.id !== this.user.id);
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
                });
                Echo.join('banned')
                 .listen('UserBanned',(event) => {
                  if (this.user.id === event.user.id) {
                      Echo.disconnect();
                      window.location.replace("/home");
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
