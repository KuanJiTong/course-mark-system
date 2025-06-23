<template>
  <div class="advisor-rank">
    <h1>View Advisee Ranking/Position</h1>
    <div class="form-group">
      <label for="advisee">Advisee:</label>
      <select v-model="selectedAdviseeId" @change="onAdviseeChange" required>
        <option disabled value="">-- Select Advisee --</option>
        <option v-for="advisee in advisees" :key="advisee.student_id" :value="advisee.student_id">
          {{ advisee.student_name }} ({{ advisee.matric_no }})
        </option>
      </select>
    </div>
    <div class="form-group" v-if="studentEnrollments.length">
      <label for="course">Course:</label>
      <select v-model="selectedCourseId" @change="onCourseChange" required>
        <option disabled value="">-- Select Course --</option>
        <option v-for="course in uniqueCourses" :key="course.course_id" :value="course.course_id">
          {{ course.course_name }}
        </option>
      </select>
    </div>
    <div class="form-group" v-if="filteredSections.length">
      <label for="section">Section:</label>
      <select v-model="selectedSectionId" @change="fetchRank" required>
        <option disabled value="">-- Select Section --</option>
        <option v-for="section in filteredSections" :key="section.section_id" :value="section.section_id">
          Section {{ section.section_number }}
        </option>
      </select>
    </div>
    <div v-if="rankInfo && selectedAdviseeId">
      <h2 class="highlight">{{ getAdviseeName(selectedAdviseeId) }}'s Rank: {{ rankInfo.rank }} / {{ rankInfo.total_students }}</h2>
      <h2 class="highlight">Percentile: {{ rankInfo.percentile }}%</h2>
    </div>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      userID: 1, 
      advisees: [],
      selectedAdviseeId: '',
      studentEnrollments: [],
      selectedCourseId: '',
      selectedSectionId: '',
      rankInfo: null,
      errorMessage: ''
    };
  },
  computed: {
    uniqueCourses() {
      const map = {};
      this.studentEnrollments.forEach(e => { map[e.course_id] = e; });
      return Object.values(map);
    },
    filteredSections() {
      return this.studentEnrollments.filter(e => e.course_id == this.selectedCourseId);
    }
  },
  methods: {
    async fetchAdvisees() {
      const res = await fetch(`http://localhost:3000/advisor/advisees?advisor_id=${this.userID}`);
      this.advisees = await res.json();
    },
    async fetchStudentEnrollments() {
      if (!this.selectedAdviseeId) return;
      const res = await fetch(`http://localhost:3000/student/enrollments?student_id=${this.selectedAdviseeId}`);
      this.studentEnrollments = await res.json();
      this.selectedCourseId = '';
      this.selectedSectionId = '';
      this.rankInfo = null;
    },
    async onAdviseeChange() {
      await this.fetchStudentEnrollments();
    },
    async onCourseChange() {
      this.selectedSectionId = '';
      this.rankInfo = null;
    },
    async fetchRank() {
      if (!this.selectedAdviseeId || !this.selectedCourseId || !this.selectedSectionId) return;
      try {
        const url = `http://localhost:3000/student/rank?student_id=${this.selectedAdviseeId}&course_id=${this.selectedCourseId}&section_id=${this.selectedSectionId}`;
        const res = await fetch(url);
        if (!res.ok) {
          this.errorMessage = 'Failed to load rank (server error).';
          return;
        }
        this.rankInfo = await res.json();
      } catch (err) {
        this.errorMessage = 'Failed to load rank (network error).';
      }
    },
    getAdviseeName(student_id) {
      const found = this.advisees.find(a => a.student_id == student_id);
      return found ? found.student_name : `Student`;
    }
  },
  async mounted() {
    await this.fetchAdvisees();
  }
};
</script>

<style scoped>
.advisor-rank {
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
.highlight {
  background-color: #fff3cd !important;
  font-weight: bold;
}
</style> 