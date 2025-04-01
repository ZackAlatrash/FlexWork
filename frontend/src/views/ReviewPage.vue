<template>
  <div class="review-page-container">
    <div class="freelancer-header">
      <div class="freelancer-info">
        <div class="avatar-placeholder">👤</div>
        <div class="name-rating">
          <h2>{{ freelancer.name }}</h2>
          <StarRating 
           v-model="averageRating"
           :starSize="32"
           starColor="#ff9800"
           inactiveColor="#333333"
           :numberOfStars="5"
           :disableClick="true" 
          />
          <p>{{ averageRating }}</p>
          <p>{{ reviews.length }} review(s)</p>
        </div>
      </div>
    </div>

    <div v-if="canReview" class="submit-review-form">
      <h3>Leave a Review</h3>
      <StarRating 
       v-model="rating"
       :starSize="32"
       starColor="#ff9800"
       inactiveColor="#333333"
       :numberOfStars="5"
       :disableClick="false" 
      />

      <textarea v-model="reviewText" placeholder="Write your feedback..."></textarea>
      <button @click="submitReview">Submit Review</button>
    </div>

    <div class="review-list">
      <h3>What Clients Say</h3>
      <div v-if="reviews.length">
        <div class="review-card" v-for="review in reviews" :key="review.id">
          <div class="review-header">
            <strong>{{ review.client_name }}</strong>
            <StarRating 
             v-model="review.rating"
             :starSize="32"
             starColor="#ff9800"
             inactiveColor="#ddd"
             :numberOfStars="5"
             :disableClick="true" 
            />
          </div>
          <p class="review-text">"{{ review.review }}"</p>
          <p class="review-date">{{ formatDate(review.created_at) }}</p>
        </div>
      </div>
      <p v-else>No reviews yet.</p>
    </div>
  </div>
</template>

<script>
import api from "@/api/api";
import { useAuthStore } from "@/stores/authStore";
import StarRating from "vue3-star-ratings";

export default {
  name: "ReviewPage",
  components: { StarRating },
  setup() {
    const authStore = useAuthStore();
    return { authStore };
  },
  data() {
    return {
      freelancer: {},
      reviews: [],
      averageRating: 0,
      rating: 0,
      reviewText: "",
      canReview: false,
    };
  },
  computed: {
    freelancerId() {
      return this.$route.params.id;
    },
    userId() {
      return this.authStore.userId;
    },
  },
  methods: {
    formatDate(date) {
      return new Date(date).toLocaleDateString();
    },
    async fetchReviews() {
      try {
        const response = await api.get(`/api/reviews/${this.freelancerId}`);
        this.reviews = response.data.reviews;
      } catch (error) {
        console.error("Error fetching reviews:", error);
      }
    },
    async fetchAverageRating(){
      try {
        const response = await api.get(`/api/reviews/${this.freelancerId}/average`);
        this.averageRating = Number(response.data.average_rating);
      } catch (error) {
        console.error("Error fetching average rating:", error);
      }
    },
    async fetchFreelancerName() {
      try {
        const response = await api.get(`/api/users/${this.freelancerId}`);
        this.freelancer = response.data;
      } catch (error) {
        console.error("Error fetching freelancer info:", error);
      }
    },
    async checkIfUserHasReviewed() {
      try {
        const token = localStorage.getItem("jwt");
        const response = await api.get(
          `/api/reviews/${this.freelancerId}/can-review`,
          { headers: { Authorization: `Bearer ${token}` } }
        );
        this.canReview = !response.data.hasReviewed;
      } catch (error) {
        console.error("Check failed:", error);
        this.canReview = false;
      }
    },
    async submitReview() {
      try {
        console.log("Submitting review:", this.rating, this.reviewText);
        const token = localStorage.getItem("jwt");
        const response = await api.post(
          "/api/reviews",
          {
            freelancer_id: this.freelancerId,
            job_id: this.$route.query.job_id,
            rating: this.rating,
            review: this.reviewText,
          },
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        this.reviewText = "";
        this.rating = 0;
        this.canReview = false;
        this.fetchReviews();
        console.log("Review submitted:", response.data);
      } catch (error) {
        console.error("Error submitting review:", error);
      }
    },
    async checkIfCanSubmitReview() {
      const token = localStorage.getItem("jwt");
      const jobId = this.$route.query.job_id;

      if (!jobId) {
        this.canReview = false;
        return;
      }
    
      try {
        const acceptedRes = await api.get(`/api/jobs/${jobId}/accepted_application`, {
          headers: { Authorization: `Bearer ${token}` },
        });
      
        const accepted = acceptedRes.data;
        const isForThisFreelancer = accepted.freelancer_id == this.freelancerId;
      
        if (!isForThisFreelancer) {
          this.canReview = false;
          return;
        }
      
        const reviewRes = await api.get(`/api/reviews/${this.freelancerId}/can-review`, {
          headers: { Authorization: `Bearer ${token}` },
        });
      
        this.canReview = !reviewRes.data.hasReviewed;
      } catch (error) {
        this.canReview = false;
        console.error("Can't submit review:", error);
      }
    },

  },
  mounted() {
    this.fetchReviews();
    this.fetchFreelancerName();
    this.fetchAverageRating();
    this.checkIfCanSubmitReview();
  },
};
</script>

<style>
.review-page-container {
  max-width: 800px;
  margin: 30px auto;
  padding: 20px;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.freelancer-header {
  display: flex;
  align-items: center;
  margin-bottom: 30px;
}

.freelancer-info {
  display: flex;
  align-items: center;
  gap: 20px;
}

.avatar-placeholder {
  width: 60px;
  height: 60px;
  background: #ccc;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

.name-rating h2 {
  margin: 0;
  color: #222831;
}

.name-rating p {
  color: #888;
  margin: 4px 0 0;
}

.submit-review-form {
  margin-bottom: 40px;
}

textarea {
  width: 100%;
  padding: 10px;
  margin-top: 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
  resize: none;
  min-height: 80px;
}

button {
  margin-top: 10px;
  padding: 10px 20px;
  background-color: #00ADB5;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

button:hover {
  background-color: #008B8B;
}

.review-list {
  margin-top: 20px;
}

.review-card {
  background: #f8f9fa;
  padding: 15px;
  margin-bottom: 15px;
  border-left: 5px solid #00ADB5;
  border-radius: 5px;
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.review-text {
  font-style: italic;
  margin: 10px 0;
}

.review-date {
  font-size: 12px;
  color: #777;
  text-align: right;
}
</style>
