<template>
  <div class="student-marks">
    <h1>My Marks</h1>
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
      <select v-model="selectedSectionId" @change="fetchMarks" required>
        <option disabled value="">-- Select Section --</option>
        <option v-for="section in sections" :key="section.section_id" :value="section.section_id">
          Section {{ section.section_number }}
        </option>
      </select>
    </div>
    <table v-if="marks.length">
      <thead>
        <tr>
          <th>Component</th>
          <th>Mark</th>
          <th>Max Mark</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="mark in marks" :key="mark.mark_id">
          <td>{{ mark.component_name }}</td>
          <td>{{ mark.mark }}</td>
          <td>{{ mark.max_mark }}</td>
        </tr>
      </tbody>
    </table>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      studentID: null, // Will be set from sessionStorage
      courses: [],
      sections: [],
      marks: [],
      selectedCourseId: '',
      selectedSectionId: '',
      errorMessage: ''
    };
  },
  computed: {
    studentIdVar() {
      return this.studentID;
    }
  },
  methods: {
    // Get student_id from user_id
    async getStudentIdFromUserId() {
      const userData = sessionStorage.getItem('user');
      if (userData) {
        const user = JSON.parse(userData);
        const userId = user.user_id;
        try {
          const res = await fetch(`http://localhost:3000/student-id?user_id=${userId}`);
          if (!res.ok) throw new Error('Failed to fetch student_id');
          const data = await res.json();
          this.studentID = data.student_id;
          return true;
        } catch (err) {
          this.errorMessage = 'Failed to get student ID.';
          return false;
        }
      }
      return false;
    },
    async fetchCourses() {
      try {
        const res = await fetch('http://localhost:3000/courses');
        if (!res.ok) {
          this.errorMessage = 'Server error loading courses';
          return;
        }
        this.courses = await res.json();
        // Auto-select first course if available
        if (this.courses.length && !this.selectedCourseId) {
          this.selectedCourseId = this.courses[0].course_id;
          await this.fetchSections();
        }
      } catch (err) {
        this.errorMessage = 'Failed to load courses.';
      }
    },
    async fetchSections() {
      try {
        this.sections = [];
        const res = await fetch(`http://localhost:3000/sections?course_id=${this.selectedCourseId}`);
        this.sections = await res.json();
        // Auto-select first section if available
        if (this.sections.length && !this.selectedSectionId) {
          this.selectedSectionId = this.sections[0].section_id;
          await this.fetchMarks();
        }
      } catch {
        this.errorMessage = 'Failed to load sections.';
      }
    },
    async fetchMarks() {
      // Guard: Only fetch if all required params are set
      if (!this.studentID || !this.selectedCourseId || !this.selectedSectionId) {
        this.errorMessage = 'Please select a course and section.';
        return;
      }
      try {
        this.marks = [];
        const url = `http://localhost:3000/student/marks?student_id=${this.studentID}&course_id=${this.selectedCourseId}&section_id=${this.selectedSectionId}`;
        console.log('Fetching marks with:', this.studentID, this.selectedCourseId, this.selectedSectionId);
        const res = await fetch(url);
        if (!res.ok) {
          this.errorMessage = 'Failed to load marks (server error).';
          return;
        }
        const data = await res.json();
        this.marks = Array.isArray(data.marks) ? data.marks : [];
      } catch (err) {
        this.errorMessage = 'Failed to load marks (network error).';
      }
    }
  },
  mounted() {
    this.getStudentIdFromUserId().then(success => {
      if (success) {
        this.fetchCourses();
      } else {
        this.errorMessage = 'Authentication required. Please login.';
        this.$router.push('/login');
      }
    });
  }
};
</script>

<style scoped>
.student-marks {
  max-width: 700px;
  margin: auto;
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
  font-weight: bold;
}
select {
  width: 100%;
  padding: 8px;
  font-size: 16px;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
th, td {
  border: 1px solid #ccc;
  padding: 10px;
  text-align: center;
}
th {
  background-color: #f5f5f5;
}
.error-message {
  color: red;
  margin-top: 10px;
}
</style> 