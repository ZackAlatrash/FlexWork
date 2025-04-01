<template>
  <div class="job-detail">
    <h1>{{ job.title }}</h1>
    <p><strong>{{ job.description }}</strong></p>
    <p>{{ job.additional_details }}</p>

    <div class="job-meta">
      <p><strong>Category:</strong> {{ job.category }}</p>
      <p><strong>Budget:</strong> ${{ job.budget }}</p>
      <p><strong>Deadline:</strong> {{ job.deadline }}</p>
      <p><strong>Posted by:</strong> {{ job.client_name }}</p>
    </div>

  
    <div v-if="isFreelancer">
      <h3>Apply for this Job</h3>
      <textarea v-model="proposal" placeholder="Write your proposal"></textarea>
      <input type="number" v-model="bid_amount" placeholder="Enter your bid amount">
      <button @click="applyForJob">Submit Application</button>
    </div>

    <div v-if="isClientOrAdmin" class="applications-container">
      <h3>Job Applications</h3>

      <div v-if="applications.length">
        <div v-for="app in applications" :key="app.id" class="application-card">
          <div class="application-header">
            <h4>{{ app.freelancer_name }}</h4>
            <span class="bid-amount">Bid: ${{ app.bid_amount }}</span>
          </div>
          <p class="proposal-text">"{{ app.proposal }}"</p>
          <p class="application-date">📅 Applied on: {{ app.created_at }}</p>
          <p><strong>Status:</strong> {{ app.status }}</p>
          <button @click="goToReviewPage(app.freelancer_id)" class="rating_button">View Rating</button>

          <div v-if="isJobOwner && app.status === 'pending'">
            <button @click="acceptApplicationAndStartJob(app.id)" class="accept-btn">
              Accept
            </button>
            <button @click="updateApplicationStatus(app.id, 'rejected')" class="reject-btn">
              Reject
            </button>
          </div>

          <p v-else class="status-msg">Application {{ app.status }}</p>
        </div>
      </div>
      <p v-else class="no-applications">No applications yet.</p>
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
      job: {},
      applications: [],
      proposal: "",
      bid_amount: 0,
    };
  },
  computed: {
    isFreelancer() {
      return this.authStore.isFreelancer;
    },
    isClientOrAdmin() {
      return this.authStore.isClientOrAdmin;
    },
    isJobOwner() {
      return this.authStore.role === "client" && this.authStore.id === this.job.client_id;
    },
  },
  methods: {
    async fetchJobDetails() {
      try {
        const jobId = this.$route.params.id;
        const response = await api.get(`/api/jobs/${jobId}`);
        this.job = response.data;
      } catch (error) {
        console.error("Error fetching job details:", error);
      }
    },
    async fetchApplications() {
      try {
        const jobId = this.$route.params.id;
        const response = await api.get(`/api/jobs/${jobId}/applications`);
        this.applications = response.data.applications;
      } catch (error) {
        console.error("Error fetching applications:", error);
      }
    },
    async applyForJob() {
      if (!this.proposal.trim() || !this.bid_amount) {
        alert("Please fill out both your proposal and bid amount before submitting.");
        return;
      }
    
      try {
        const jobId = this.$route.params.id;
        const response = await api.post(`/api/jobs/${jobId}/apply`, {
          proposal: this.proposal,
          bid_amount: this.bid_amount,
        });
        console.log("Application submitted:", response.data);
      
        alert("Application submitted successfully!");
        this.proposal = "";
        this.bid_amount = 0;
        this.fetchApplications();
      } catch (error) {
        console.error("Error applying for job:", error);
        alert("Failed to submit application. Please try again.");
      }
    },

    async updateApplicationStatus(applicationId, newStatus) {
      try {
        const token = localStorage.getItem("jwt");
        const response = await api.put(
          `/api/applications/${applicationId}/status`,
          { status: newStatus },
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        console.log("Application updated:", response.data);

        // Update UI instantly
        const appIndex = this.applications.findIndex((app) => app.id === applicationId);
        if (appIndex !== -1) {
          this.applications[appIndex].status = newStatus;
        }
      } catch (error) {
        console.error("Error updating application status:", error);
      }
    },
    async updateJobStatus(newStatus) {
      try {
        const jobId = this.$route.params.id;
        const token = localStorage.getItem("jwt");
        const response = await api.put(
          `/api/jobs/${jobId}/status`,
          { job_status: newStatus },
          { headers: { Authorization: `Bearer ${token}` } }
        );

        console.log("Job status updated:", response.data);
      } catch (error) {
        console.error("Error updating job status:", error);
      }
    },
    goToReviewPage(freelancerId) {
      this.$router.push(`/freelancer/${freelancerId}/reviews`);
    },
    async acceptApplicationAndStartJob(applicationId) {
      try {
        await this.updateApplicationStatus(applicationId, 'accepted');
        await this.updateJobStatus('in_progress');
      } catch (error) {
        console.error("Failed to accept application and update job:", error);
      }
    },

  },
  mounted() {
    this.fetchJobDetails();
    this.fetchApplications();
  },
};
</script>
<style scoped>
.job-detail {
  max-width: 800px;
  margin: 30px auto;
  padding: 20px;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
h1 {
  color: #222831;
}
.job-meta {
  margin-top: 15px;
  padding: 10px;
  background: #f8f9fa;
  border-radius: 5px;
}
textarea,
input {
  width: 100%;
  padding: 8px;
  margin-top: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
}
button {
  padding: 10px;
  background: #00ADB5;
  border: none;
  border-radius: 5px;
  color: white;
  font-size: 16px;
  cursor: pointer;
  margin-top: 10px;
}
button:hover {
  background: #008B8B;
}
.applications-container {
  margin-top: 20px;
}

.application-card {
  background: #f9f9f9;
  border-left: 5px solid #00ADB5;
  padding: 15px;
  margin: 10px 0;
  border-radius: 5px;
  box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
}

.application-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.bid-amount {
  font-size: 14px;
  font-weight: bold;
  color: #008B8B;
}

.proposal-text {
  font-style: italic;
  color: #555;
}

.application-date {
  font-size: 12px;
  color: #777;
}

.accept-btn {
  background: #4caf50;
  color: white;
  padding: 8px 12px;
  border: none;
  margin-right: 5px;
  cursor: pointer;
}

.reject-btn {
  background: #ff4c4c;
  color: white;
  padding: 8px 12px;
  border: none;
  cursor: pointer;
}

.status-msg {
  font-weight: bold;
  color: #555;
}

.no-applications {
  color: #888;
  font-style: italic;
}

.rating_button {
  background: #00ADB5;
  color: white;
  padding: 8px 12px;
  border: none;
  cursor: pointer;
  margin-top: 10px;
  float: right;
}
</style>
