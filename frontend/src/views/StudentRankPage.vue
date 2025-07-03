<template>
  <div class="student-rank">
    <h1>My Class Rank & Percentile</h1>
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
      <select v-model="selectedSectionId" @change="fetchRank" required>
        <option disabled value="">-- Select Section --</option>
        <option v-for="section in sections" :key="section.section_id" :value="section.section_id">
          Section {{ section.section_number }}
        </option>
      </select>
    </div>
    <div v-if="rankInfo">
      <h2>Your Rank: {{ rankInfo.rank }} / {{ rankInfo.total_students }}</h2>
      <h2>Percentile: {{ rankInfo.percentile }}%</h2>
    </div>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      studentID: null,
      courses: [],
      sections: [],
      selectedCourseId: '',
      selectedSectionId: '',
      rankInfo: null,
      errorMessage: ''
    };
  },
  methods: {
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
      } catch (err) {
        this.errorMessage = 'Failed to load courses.';
      }
    },
    async fetchSections() {
      try {
        this.sections = [];
        const res = await fetch(`http://localhost:3000/sections?course_id=${this.selectedCourseId}`);
        this.sections = await res.json();
      } catch {
        this.errorMessage = 'Failed to load sections.';
      }
    },
    async fetchRank() {
      try {
         const url = `http://localhost:3000/student/rank?student_id=${this.studentID}&course_id=${this.selectedCourseId}&section_id=${this.selectedSectionId}`;
        const res = await fetch(url);
        if (!res.ok) {
          this.errorMessage = 'Failed to load rank (server error).';
          return;
        }
        this.rankInfo = await res.json();
      } catch (err) {
        this.errorMessage = 'Failed to load rank (network error).';
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
.student-rank {
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
.error-message {
  color: red;
  margin-top: 10px;
}
</style> 