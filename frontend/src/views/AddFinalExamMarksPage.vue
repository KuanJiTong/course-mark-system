<template>
  <div class="final-exam-marks">
    <h1>Add Final Exam Marks ({{ maxFinalMark }}%)</h1>

    <!-- Course and Section Selection -->
    <div class="form-group">
      <label for="course">Course:</label>
      <select v-model="selectedCourseId" @change="fetchSections" required>
        <option disabled value="">-- Select Course --</option>
        <option v-for="course in courses" :key="course.course_id" :value="course.course_id">
          {{ course.course_name }}
        </option>
      </select>
    </div>

    <div class="form-group" v-if="sections.length">
      <label for="section">Section:</label>
      <select v-model="selectedSectionId" @change="fetchStudents" required>
        <option disabled value="">-- Select Section --</option>
        <option v-for="section in sections" :key="section.section_id" :value="section.section_id">
          Section {{ section.section_number }}
        </option>
      </select>
    </div>

    <!-- Student and Marks Entry -->
    <form @submit.prevent="submitMarks" v-if="students && students.length">
      <div class="form-group">
        <label for="studentId">Student:</label>
        <select v-model="studentId" required>
          <option disabled value="">-- Select Student --</option>
          <option v-for="student in students" :key="student.student_id" :value="student.student_id">
            {{ student.student_id }} - {{ student.student_name }}
          </option>
        </select>
      </div>

      <div class="form-group">
        <label for="marks">Final Exam Mark (out of {{ maxFinalMark }}):</label>
<input
  type="number"
  id="marks"
  v-model.number="marks"
  :max="maxFinalMark"
  min="0"
  required
/>
      </div>

      <button type="submit">💾 Save Marks</button>
    </form>

    <!-- Messages -->
    <div v-if="successMessage" class="success-message">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="error-message">
      {{ errorMessage }}
    </div>
  </div>
</template>


<script>
// import DefaultLayout from '../components/DefaultLayout.vue';

export default {
  // components: { DefaultLayout },
  data() {
    return {
      userID: null,
      selectedCourseId: '',
      selectedSectionId: '',
      courses: [],
      sections: [],
      students: [],
      studentId: '',
      marks: '',
      successMessage: '',
      errorMessage: ''
    };
  },
  computed: {
  selectedCourse() {
    return this.courses.find(c => c.course_id === this.selectedCourseId) || null;
  },
  maxFinalMark() {
    return this.selectedCourse?.max_fm;
  }
},
  methods: {
    async fetchCourses() {
      try {
        const res = await fetch('http://localhost:3000/courses');
        this.courses = await res.json();
      } catch (err) {
        this.errorMessage = 'Failed to load courses.';
      }
    },
    async fetchSections() {
      try {
        const res = await fetch(`http://localhost:3000/sections?course_id=${this.selectedCourseId}`);
        this.sections = await res.json();
      } catch (err) {
        this.errorMessage = 'Failed to load sections.';
      }
    },
    async fetchStudents() {
      try {
        const res = await fetch(`http://localhost:3000/students?course_id=${this.selectedCourseId}&section_id=${this.selectedSectionId}`);
        this.students = await res.json();
      } catch (err) {
        this.errorMessage = 'Failed to load students.';
      }
    },
    async submitMarks() {
      this.successMessage = '';
      this.errorMessage = '';

      if (!this.selectedCourseId || !this.selectedSectionId || !this.studentId || this.marks === '') {
        this.errorMessage = 'All fields are required.';
        return;
      }

      const payload = {
        student_id: this.studentId,
        course_id: this.selectedCourseId,
        section_id: this.selectedSectionId,
        mark: this.marks
      };

      try {
        const res = await fetch('http://localhost:3000/final_exam', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (res.ok) {
          this.successMessage = data.message || 'Final exam mark saved.';
          this.studentId = '';
          this.marks = '';
        } else {
          this.errorMessage = data.error || 'Failed to save final exam mark.';
        }
      } catch (err) {
        this.errorMessage = 'Error submitting data.';
      }
    },
    async fetchExistingMark() {
  if (!this.studentId || !this.selectedCourseId || !this.selectedSectionId) return;

  try {
    const res = await fetch(`http://localhost:3000/final_exam/student?student_id=${this.studentId}&course_id=${this.selectedCourseId}&section_id=${this.selectedSectionId}`);
    const data = await res.json();
    this.marks = data.mark ?? ''; // Set marks to value or empty string
  } catch (err) {
    console.error("Failed to fetch existing mark", err);
    this.errorMessage = 'Could not fetch existing mark.';
  }
}
  },
  mounted() {
    // Check authentication
    const user = JSON.parse(sessionStorage.getItem('user'));
    if (!user || !user.user_id) {
      this.$router.push('/login?message=Please login to access final exam marks');
      return;
    }
    
    this.userID = user.user_id;
    console.log('Authenticated user ID for final exam marks:', this.userID);
    
    this.fetchCourses();
  },
  watch: {
  studentId(newVal) {
    if (newVal) {
      this.fetchExistingMark();
    } else {
      this.marks = '';
    }
  }
}
};
</script>

<style scoped>
.final-exam-marks {
  padding: 20px;
  max-width: 600px;
  margin: auto;
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
  font-weight: bold;
}
input, select {
  width: 100%;
  padding: 8px;
  font-size: 16px;
}
button {
  background-color: #007bff;
  color: white;
  padding: 10px 20px;
  font-size: 16px;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}
button:hover {
  background-color: #0056b3;
}
.success-message {
  margin-top: 15px;
  color: green;
}
.error-message {
  margin-top: 15px;
  color: red;
}
</style>
