<template>
  <div class="my-jobs-container">
    <h1>My Jobs</h1>

    <div v-if="isClient">
      <h2>Jobs I Posted</h2>
      <ul v-if="clientJobs.length">
        <li v-for="job in clientJobs" :key="job.id" class="job-card">
          <h3>{{ job.title }}</h3>
          <p><strong>Budget:</strong> ${{ job.budget }}</p>
          <p><strong>Status:</strong> {{ job.job_status }}</p>
          <button @click="goToJobDetail(job.id)">View Job</button>

          <button @click="toggleStatusDropdown(job.id)">
            {{ job.showDropdown ? "Done" : "Change Status" }}
          </button>
          <button style="background-color: #25D366;" @click="goToMessages(job.id)" v-if="job.job_status === 'in_progress'">
            Message
          </button>
          <button
            v-if="job.job_status === 'completed' && job.freelancer_id"
            @click="leaveAReview(job.id, job.freelancer_id)"
          >
            Leave Review
          </button>

          <transition name="slide-fade">
            <select v-if="job.showDropdown" v-model="job.newStatus" @change="updateJobStatus(job)">
              <option value="" disabled>Select Status</option>
              <option value="open">Open</option>
              <option value="completed">Completed</option>
            </select>
          </transition>
        </li>
      </ul>
      <p v-else>You haven't posted any jobs yet.</p>
    </div>

    <div v-if="isFreelancer">
      <h2>Jobs I'm Working On</h2>
      <ul v-if="freelancerJobs.length">
        <li v-for="job in freelancerJobs" :key="job.id" class="job-card">
          <h3>{{ job.title }}</h3>
          <p><strong>Status:</strong> {{ job.job_status }}</p>
          <p><strong>Deadline:</strong> {{ job.deadline }}</p>
          <button @click="goToJobDetail(job.id)">View Job</button>
          <button style="background-color: #25D366;" @click="goToMessages(job.id)">
            Message
          </button>
          
        </li>
      </ul>
      <p v-else>You haven't applied for any jobs yet.</p>
    </div>
  </div>
</template>

<script>
import api from "@/api/api";
import { useAuthStore } from "@/stores/authStore";

export default {
  setup() {
    const authStore = useAuthStore();
    return { authStore };
  },
  data() {
    return {
      clientJobs: [],
      freelancerJobs: [],
    };
  },
  computed: {
    isClient() {
      return this.authStore.role === "client";
    },
    isFreelancer() {
      return this.authStore.role === "freelancer";
    },
  },
  methods: {
    async getClientJobs() {
      try {
        const token = localStorage.getItem("jwt");
        const response = await api.get("/api/my-jobs/client", {
          headers: { Authorization: `Bearer ${token}` },
        });

        this.clientJobs = await Promise.all(
          response.data.jobs.map(async (job) => {
            let freelancer_id = null;
            if (job.job_status === "completed") {
              try {
                const app = await this.getAcceptedApplication(job.id);
                freelancer_id = app.freelancer_id;
              } catch (e) {
                console.warn(`No accepted app found for job ${job.id}`);
              }
            }
            return {
              ...job,
              showDropdown: false,
              newStatus: job.job_status,
              freelancer_id,
            };
          })
        );
      } catch (error) {
        console.error("Error fetching client jobs:", error);
      }
    },
    async getAcceptedApplication(jobId) {
      const token = localStorage.getItem("jwt");
      const response = await api.get(`/api/jobs/${jobId}/accepted_application`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      return response.data;
    },
    async getFreelancerJobs() {
      try {
        const token = localStorage.getItem("jwt");
        const response = await api.get("/api/my-jobs/freelancer", {
          headers: { Authorization: `Bearer ${token}` },
        });
        this.freelancerJobs = response.data.jobs;
      } catch (error) {
        console.error("Error fetching freelancer jobs:", error);
      }
    },
    async fetchJobs() {
      if (this.isClient) {
        await this.getClientJobs();
      } else if (this.isFreelancer) {
        await this.getFreelancerJobs();
      }
    },
    goToJobDetail(jobId) {
      this.$router.push({ path: `/jobs/${jobId}` });
    },
    toggleStatusDropdown(jobId) {
      const job = this.clientJobs.find((j) => j.id === jobId);
      if (job) {
        job.showDropdown = !job.showDropdown;
      }
    },
    async updateJobStatus(job) {
      try {
        const token = localStorage.getItem("jwt");
        const response = await api.put(
          `/api/jobs/${job.id}/status`,
          { job_status: job.newStatus },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        this.fetchJobs(); 

        console.log("Job status updated:", response.data);
        job.job_status = job.newStatus;
        job.showDropdown = false;
      } catch (error) {
        console.error("Error updating job status:", error);
      }
    },
    goToMessages(jobId) {
      this.$router.push({ path: `/job/messages/`, query: { job_id: jobId } });
    },
    leaveAReview(jobId, freelancerId) {
      this.$router.push({
        path: `/freelancer/${freelancerId}/reviews`,
        query: { job_id: jobId },
      });
    },
  },
  mounted() {
    this.fetchJobs();
  },
};
</script>

<style scoped>
.my-jobs-container {
  max-width: 800px;
  margin: 30px auto;
  padding: 20px;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
ul {
  list-style: none;
  padding: 0;
}

h1,
h2 {
  text-align: center;
  color: #222831;
}

.job-card {
  background: #f8f9fa;
  padding: 15px;
  margin: 10px 0;
  border-radius: 5px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

button {
  padding: 8px 15px;
  background: #00ADB5;
  border: none;
  border-radius: 5px;
  color: white;
  cursor: pointer;
  margin-right: 20px;
}

button:hover {
  background: #008B8B;
}

.slide-fade-enter-active {
  transition: all 0.3s ease-in-out;
}
.slide-fade-leave-active {
  transition: all 0.2s ease-in-out;
}
.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

select {
  display: block;
  width: 100%;
  margin-top: 10px;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 14px;
  background: white;
}
</style>
