<template>
    <div class="messages-container">
      <h2>Messages</h2>
  
      <div class="message-box">
        <ul>
          <li v-for="msg in messages" :key="msg.id" :class="{'my-message': msg.sender_id === this.userId}">
            <span class="sender">{{ msg.sender_id === this.userId ? "You" : msg.sender_name }}</span>
                <p>{{ msg.message }}</p>
            <span class="timestamp">{{ formatDate(msg.sent_at) }}</span>
          </li>
        </ul>
      </div>
  
      <div class="message-input">
        <textarea v-model="newMessage" placeholder="Type your message..."></textarea>
        <button @click="sendMessage">Send</button>
      </div>
    </div>
  </template>

  <script>
    import api from "@/api/api";
    import { useAuthStore } from "@/stores/authStore";
    //import { useRouter } from "vue-router";

    export default {
      name: "JobMessages",
      setup() {
            const authStore = useAuthStore();
            return { authStore };
        },
      data() {
        return {
            messages: [],
            newMessage: "",
            job: {},
            acceptedApplication: {},
          
        };
      },
      computed: {
        isClient() {
          return this.authStore.role === "client";
        },
        isFreelancer() {
          return this.authStore.role === "freelancer";
        },
        userId() {
          return this.authStore.userId;
        },
        receiverId() {
            if (this.isFreelancer) {
                this.fetchJobDetails();
                return this.job.client_id;
            } else if (this.isClient) {
                this.fetchAcceptedApplication();
                return this.acceptedApplication.freelancer_id;
            }
            return null;
        }

      },
      methods: {
        formatDate(date) {
          return new Date(date).toLocaleString();
        },
        async sendMessage() {
            const jobId = this.$route.query.job_id;

            
            const response = await api.post(`/api/messages/send`, {
                job_id: jobId,
                receiver_id: this.receiverId,
                message: this.newMessage,
           });
            this.messages.push(response.data);
            this.newMessage = "";
            this.fetchMessages();
        },
        async fetchMessages() {
            try{
                const jobId = this.$route.query.job_id;
                const response = await api.get(`/api/messages/${jobId}`);
                this.messages = response.data.messages;
                console.log(response.data);
            } catch (error) {
                console.log(error);
            }
          
        },
        async fetchJobDetails() {
            try {
                const jobId = this.$route.query.job_id;
                const response = await api.get(`/api/jobs/${jobId}`);
                this.job = response.data; 
            } catch (error) {
                console.error("Error fetching job details:", error);
            }
        },
        async fetchAcceptedApplication() {
            try {
                const jobId = this.$route.query.job_id;
                const response = await api.get(`/api/jobs/${jobId}/accepted_application`);
                console.log("application", response.data);
                this.acceptedApplication = response.data;
                
            } catch (error) {
                console.error("Error fetching accepted application:", error);
            }
        }

      },
      mounted() {
         this.fetchMessages();
         console.log("receiver id",this.receiverId);
         
      },
    };


</script>

<style scoped>
.messages-container {
  max-width: 600px;
  margin: auto;
  padding: 20px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

h2 {
  text-align: center;
  color: #222831;
}

.message-box {
  max-height: 300px;
  overflow-y: auto;
  border: 1px solid #ccc;
  padding: 10px;
  background: #f9f9f9;
  border-radius: 5px;
}

ul {
  list-style: none;
  padding: 0;
}

li {
  padding: 10px;
  border-radius: 5px;
  margin-bottom: 5px;
}

.my-message {
  background: #25D366  ;
  color: white;
  text-align: right;
  border-radius: 5px;
  padding: 8px;
  margin: 5px 0;
}

.timestamp {
  font-size: 12px;
  color: gray;
  display: block;
  text-align: right;
}

.message-input {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

textarea {
  flex: 1;
  padding: 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
  resize: none;
}

button {
  padding: 10px;
  background: #00ADB5;
  border: none;
  color: white;
  cursor: pointer;
  border-radius: 5px;
  transition: 0.3s;
}

button:hover {
  background: #008C94;
}
</style>

  