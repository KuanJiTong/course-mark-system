<template>
  <div class="student-rank">
    <h1>My Class Rank & Percentile</h1>
    <div class="form-group">
      <label for="course">Course:</label>
      <select class="form-select" v-model="selectedSectionId" @change="fetchRank" required>
        <option disabled value="">-- Select Course --</option>
        <option v-for="course in courses" :key="course.sectionId" :value="course.sectionId">
          {{ course.courseCode }}-{{ course.sectionNumber }} {{ course.courseName }}
        </option>
      </select>
    </div>
    <div v-if="rankInfo">
      <h2>Your Rank: {{ rankInfo.rank }} / {{ rankInfo.totalStudents }}</h2>
      <h2>Percentile: {{ rankInfo.percentile }}%</h2>
    </div>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>

<script>
export default {
  data() {
    const user = JSON.parse(sessionStorage.getItem('user'));
    return {
      studentId: user.studentId,
      courses: [],
      selectedSectionId: '',
      rankInfo: null,
      errorMessage: ''
    };
  },
  async created(){
    await this.fetchCourses();
  },
  methods: {
    async fetchCourses() {
      try {
        const res = await fetch(`http://localhost:3000/enrolled-courses?student_id=${this.studentId}`);
        if (!res.ok) {
          this.errorMessage = 'Server error loading courses';
          return;
        }
        this.courses = await res.json();
        // Auto-select first course if available
        if (this.courses.length && !this.selectedSectionId) {
          this.selectedSectionId = this.courses[0].sectionId;
          await this.fetchRank();
        }
      } catch (err) {
        this.errorMessage = 'Failed to load courses.';
      }
    },
    async fetchRank() {
      try {
         const url = `http://localhost:3000/student/rank?student_id=${this.studentId}&section_id=${this.selectedSectionId}`;
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