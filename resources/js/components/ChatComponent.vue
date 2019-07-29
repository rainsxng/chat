<template>
    <div class="row">
        <div class="col-10">
            <div class="card card-default">
                <div class="card-header p-2">Messages</div>
                <ul class="list-unstyled" style="height:300px; overflow-y:scroll">
                    <li class="p-2" v-for="(message, index) in messages" :key="index">
                        <strong>{{ message.user.name }}</strong>
                        {{ message.message }}
                    </li>
                </ul>

                <input type="text" name="message" class="form-control p-2"
                       placeholder="Enter your message"
                       v-model="newMessage"
                       @keyup.enter="sendMessage"
                >

            </div>
            <span class="text-muted m-3">user is typing...</span>
        </div>
        <div class="col-2 text-center">
            <div class="card card-default">
                Users
            </div>
            <div class="card-body">
                <ul>
                    <li class="py-1">User 1</li>
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
              newMessage: ''
          }
      },
      created() {
            this.fetchMessages();
      },
        methods: {
        fetchMessages() {
                axios.get('/messages').then(response => {
                    this.messages = response.data;
            })
        },
            sendMessage() {
                this.messages.push({
                    user: this.user,
                    message: this.newMessage
                });
                axios.post('messages', {message: this.newMessage});
                this.newMessage='';
            }
      }
    }
</script>
