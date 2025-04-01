<template>
  <div class="home-container">
    <aside class="filter-panel">
      <h2>Filter Jobs</h2>

      <div class="filter-group">
        <label for="category">Category:</label>
        <select v-model="filters.category">
          <option value="">All Categories</option>
          <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Min Budget:</label>
        <input type="number" v-model="filters.min_budget" placeholder="Min Budget">
      </div>

      <div class="filter-group">
        <label>Max Budget:</label>
        <input type="number" v-model="filters.max_budget" placeholder="Max Budget">
      </div>

      <div class="filter-group">
        <label>Sort by:</label>
        <select v-model="sortOption">
          <option value="newest">Newest</option>
          <option value="budget_high">Budget: High to Low</option>
          <option value="budget_low">Budget: Low to High</option>
        </select>
      </div>

      <div class="filter-buttons">
        <button style="width: 100%;" @click="applyFilters">Apply Filters</button>
        <button style="width: 100%;" @click="resetFilters">Reset Filters</button>
      </div>

      
    </aside>

    <div class="job-list">
      <div class="job-card" v-for="job in jobs" :key="job.id" @click="showJobDetail(job.id)">
        <div class="job-info">
          <h2>{{ job.title }}</h2>
          <p>{{ job.description }}</p>
          <div class="job-meta">
            <p><strong>Budget:</strong> ${{ job.budget }}</p>
            <p><strong>Deadline:</strong> {{ job.deadline }}</p>
            <p class="category-badge">{{ job.category }}</p>
          </div>
          <div v-if="jobs.length === 0 && !isLoading" class="no-jobs">
              <p>No jobs found. Try adjusting your filters.</p>
          </div>
        </div>
      </div>

      <div class="pagination">
        <button @click="prevPage" :disabled="page === 1">Previous</button>
        <span>Page {{ page }} of {{ totalPages }}</span>
        <button @click="nextPage" :disabled="page >= totalPages">Next</button>
      </div>
    </div>
  </div>
</template>


<script>
import api from "@/api/api";

export default {
  name: "HomeView",
  data() {
    return {
      jobs: [],
      page: 1,
      limit: 10,
      totalPages: 1,
      categories: ["Web Development", "Graphic Design", "Writing", "Marketing"], 
      filters: {
        category: "",
        min_budget: "",
        max_budget: "",
      },
      sortOption: "newest",
    };
    
  },
  methods: {
    async fetchJobs() {
      try {
        const response = await api.get(`/api/jobs?page=${this.page}&limit=${this.limit}&category=${this.filters.category}&min_budget=${this.filters.min_budget}&max_budget=${this.filters.max_budget}&sort=${this.sortOption}`);
        this.jobs = response.data.jobs;
        this.totalPages = response.data.total_pages || 1;
      } catch (error) {
        console.error("Error fetching jobs:", error);
      }
    },
    
    async applyFilters() {
      this.page = 1;
      this.fetchJobs();
    },

    async resetFilters() {
      this.filters = {
        category: "",
        min_budget: "",
        max_budget: "",
      };
      this.page = 1;
      this.fetchJobs();
    },

    showJobDetail(jobid){
      this.$router.push({path: `/jobs/${jobid}`});
    },

    prevPage() {
      if (this.page > 1) {
        this.page--;
        this.fetchJobs();
      }
    },

    nextPage() {
      if (this.page < this.totalPages) {
        this.page++;
        this.fetchJobs();
      }
    },
  },
  mounted() {
    this.fetchJobs();
  },
};
</script>

<style scoped>
.home-container {
  display: flex;
  margin: auto;
  padding: 20px 0px;
  background-color: #f8f9fa; 
  min-height: 100vh;
  gap: 20px;
}

/* ✅ Sidebar Filter Panel */
.filter-panel {
  width: 280px;
  padding: 20px;
  background: #222831;
  color: #EEEEEE;
  border-radius: 10px;
}
.filter-panel h2 {
  font-size: 18px;
  margin-bottom: 10px;
}

/* ✅ Filter Groups */
.filter-group {
  margin-bottom: 15px;
}

.filter-buttons {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.filter-group label {
  font-size: 14px;
  display: block;
  margin-bottom: 5px;
}
.filter-group input, .filter-group select {
  width: 100%;
  padding: 8px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

.filter-group input {
  width: 91%;
  padding: 8px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 5px;
}


/* ✅ Apply Filters Button */
button {
  padding: 10px;
  background: #00ADB5;
  border: none;
  border-radius: 5px;
  font-size: 16px;
  cursor: pointer;
  color: white;
}
button:hover {
  background: #008B8B;
}

.job-list {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.job-card {
  background: #ffffff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-radius: 10px;
  border: 1px solid #ddd;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  cursor: pointer;
}
.job-card:hover {
  transform: scale(1.02);
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}

.job-info {
  display: flex;
  flex-direction: column;
}
.job-title {
  font-size: 20px;
  color: #222831;
}
.job-description {
  font-size: 14px;
  color: #555;
  max-width: 600px;
}

.job-meta {
  display: flex;
  gap: 20px;
  font-size: 14px;
  color: #393E46;
}
.category-badge {
  background: #00ADB5;
  color: #fff;
  padding: 5px 10px;
  border-radius: 5px;
  font-size: 14px;
  font-weight: bold;
}

/* ✅ Apply Button */
.apply-btn {
  background: #00ADB5;
  color: #ffffff;
  border: none;
  padding: 10px 15px;
  font-size: 16px;
  border-radius: 5px;
  cursor: pointer;
  transition: 0.3s;
}
.apply-btn:hover {
  background: #008B8B;
}

.pagination {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-top: 20px;
}
.pagination button {
  background: #00ADB5;
  border: none;
  padding: 10px 15px;
  border-radius: 5px;
  cursor: pointer;
  color: #ffffff;
  font-weight: bold;
}
.pagination button:disabled {
  background: #555;
  cursor: not-allowed;
}

.checkOutButton{
  background: #00ADB5;
  color: #ffffff;
  border: none;
  padding: 10px 15px;
  font-size: 16px;
  border-radius: 5px;
  cursor: pointer;
  transition: 0.3s;
}
</style>
