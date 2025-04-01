<template>
    <div class="job-form-container">
      <h1>Create a Job Listing</h1>
  
      <form @submit.prevent="submitJob">
        <!-- Job Title -->
        <div class="form-group">
          <label for="title">Job Title <span class="required">*</span></label>
          <input type="text" id="title" v-model="job.title" placeholder="Enter job title" required />
        </div>
  
        <!-- Job Description -->
        <div class="form-group">
          <label for="description">Job Description <span class="required">*</span></label>
          <textarea id="description" v-model="job.description" placeholder="Describe the job" required></textarea>
        </div>
  
        <!-- Category -->
        <div class="form-group">
          <label for="category">Category <span class="required">*</span></label>
          <select id="category" v-model.number="job.category_id" required>
            <option value="" disabled>Select a category</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>
  
        <!-- Budget -->
        <div class="form-group">
          <label for="budget">Budget ($) <span class="required">*</span></label>
          <input type="number" id="budget" v-model="job.budget" placeholder="Enter job budget" required min="1" />
        </div>
  
        <!-- Deadline -->
        <div class="form-group">
          <label for="deadline">Deadline <span class="required">*</span></label>
          <input type="date" id="deadline" v-model="job.deadline" required />
        </div>
  
        <!-- Skills (Optional) -->
        <div class="form-group">
          <label for="skills">Required Skills</label>
          <input type="text" id="skills" v-model="job.skills" placeholder="List required skills (comma-separated)" />
        </div>
  
        <!-- Location (Optional) -->
        <div class="form-group">
          <label for="location">Location</label>
          <input type="text" id="location" v-model="job.location" placeholder="Specify job location (if needed)" />
        </div>
  
        <!-- Job Type (Optional) -->
        <div class="form-group">
          <label for="job_type">Job Type</label>
          <select id="job_type" v-model="job.job_type">
            <option value="" disabled>Select job type</option>
            <option value="Full-Time">Full Time</option>
            <option value="Part-Time">Part Time</option>
            <option value="Contract">Contract</option>
            <option value="Freelance">Freelance</option>
          </select>
        </div>
  
        <!-- Additional Details (Optional) -->
        <div class="form-group">
          <label for="additional_details">Additional Details</label>
          <textarea id="additional_details" v-model="job.additional_details" placeholder="Provide extra details (optional)"></textarea>
        </div>
  
        <!-- Submit Button -->
        <button type="submit">Post Job</button>
      </form>
    </div>
  </template>

  <script>
    import api from "@/api/api";

    export default {
        data() {
            return {
                job: {
                    title: "",
                    description: "",
                    category_id: "",
                    budget: "", 
                    deadline: "",
                    skills: "",
                    location: "",
                    job_type: "",
                    additional_details: "",
                },
                categories: [],
            };
        },
        methods: {
            async getCategories(){
                try {
                    const response = await api.get("/api/categories");
                    this.categories = response.data
                } catch (error) {
                    console.error("Error fetching categories:", error);
                }
            },

            async submitJob(){
                try {
                    const token = localStorage.getItem("jwt"); 

                    if (
                          !this.job.title.trim() ||
                          !this.job.description.trim() ||
                          !this.job.category_id ||
                          isNaN(this.job.budget) ||
                          Number(this.job.budget) < 1 ||
                          !this.job.deadline
                        ) {
                          alert("Please fill all required fields with valid data!");
                          return;
                        }


                    this.job.budget = Number(this.job.budget); 

                    const response = await api.post("/api/jobs", this.job, {
                        headers: { Authorization: `Bearer ${token}` }
                    });

                    console.log("Job posted:", response.data);
                    alert("✅ Job posted successfully!");
                    this.$router.push("/");
                } catch (error) {
                  console.error("Error posting job:", error);
                  const errorMessage =
                    error?.response?.data?.message ||
                    error?.response?.data?.error ||
                    error?.response?.statusText ||
                    "Unexpected error occurred.";

                  alert("❌ Failed to post job: " + errorMessage);
                }
            }
        },
        mounted(){
            this.getCategories();
        }
    }
</script>


  <style scoped>
  .job-form-container {
    max-width: 600px;
    margin: 40px auto;
    padding: 20px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  }
  
  h1 {
    text-align: center;
    color: #222831;
    margin-bottom: 20px;
  }
  
  .form-group {
    margin-bottom: 15px;
  }
  
  label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
    color: #222;
  }
  
  .required {
    color: red;
  }
  
  input, textarea, select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
  }
  
  textarea {
    height: 100px;
    resize: vertical;
  }
  
  button {
    width: 100%;
    padding: 12px;
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
  </style>
  