<template>
    <DefaultLayout :activeTab="'final-exam'">
      <template #default>
        <div class="final-exam-marks">
          <h1>Add Final Exam Marks (30%)</h1>
          
          <!-- Form to add final exam marks -->
          <form @submit.prevent="submitMarks">
            <div class="form-group">
              <label for="studentId">Student ID:</label>
              <input type="text" id="studentId" v-model="studentId" required />
            </div>
  
            <div class="form-group">
              <label for="marks">Marks (out of 100):</label>
              <input type="number" id="marks" v-model="marks" min="0" max="100" required />
            </div>
  
            <button type="submit">Save Marks</button>
          </form>
  
          <!-- Success Message -->
          <div v-if="successMessage" class="success-message">
            <p>{{ successMessage }}</p>
          </div>
          
        </div>
      </template>
    </DefaultLayout>
  </template>
  
  <script>
  import DefaultLayout from '../components/DefaultLayout.vue';
  
  export default {
    components: {
      DefaultLayout
    },
    data() {
      return {
        studentId: '',
        marks: '',
        successMessage: ''
      };
    },
    methods: {
      // This method simulates saving marks to a database or API
      submitMarks() {
        // Simulate a successful submission (you can replace it with actual logic like API call)
        if (this.studentId && this.marks) {
          this.successMessage = `Marks for student ${this.studentId} have been successfully saved!`;
          // Optionally, clear the form after successful submission
          this.studentId = '';
          this.marks = '';
        } else {
          this.successMessage = 'Please fill in both fields.';
        }
      }
    }
  };
  </script>
  
  <style scoped>
  .final-exam-marks {
    padding: 20px;
  }
  
  h1 {
    font-size: 24px;
    margin-bottom: 20px;
  }
  
  .form-group {
    margin-bottom: 15px;
  }
  
  label {
    display: block;
    margin-bottom: 5px;
  }
  
  input {
    width: 100%;
    padding: 8px;
    font-size: 16px;
    margin-bottom: 10px;
  }
  
  button {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    cursor: pointer;
  }
  
  button:hover {
    background-color: #0056b3;
  }
  
  .success-message {
    margin-top: 20px;
    color: green;
    font-size: 18px;
  }
  </style>
  