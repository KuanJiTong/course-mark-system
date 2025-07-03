<template>
  <div class="advisor-component-averages">
    <h1>Class Average per Component (Advisor View)</h1>
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
      <select v-model="selectedSectionId" @change="fetchAveragesAndMarks" required>
        <option disabled value="">-- Select Section --</option>
        <option v-for="section in filteredSections" :key="section.section_id" :value="section.section_id">
          Section {{ section.section_number }}
        </option>
      </select>
    </div>
    <table v-if="averages.length">
      <thead>
        <tr>
          <th>Component</th>
          <th>Class Average</th>
          <th>Max Mark</th>
          <th v-if="selectedAdviseeId">Advisee Mark</th>
          <th v-if="selectedAdviseeId">Comparison</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="comp in averages" :key="comp.component_id">
          <td>{{ comp.component_name }}</td>
          <td>{{ comp.average_mark ? Number(comp.average_mark).toFixed(2) : '-' }}</td>
          <td>{{ comp.max_mark }}</td>
          <td v-if="selectedAdviseeId">{{ getAdviseeMark(comp.component_id) }}</td>
          <td v-if="selectedAdviseeId">{{ compareToAverage(comp) }}</td>
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
      userID: null,
      advisees: [],
      selectedAdviseeId: '',
      studentEnrollments: [],
      selectedCourseId: '',
      selectedSectionId: '',
      averages: [],
      adviseeMarks: [],
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
    getAuthenticatedUser() {
      const userData = sessionStorage.getItem('user');
      if (userData) {
        const user = JSON.parse(userData);
        this.userID = user.user_id;
        console.log('Authenticated advisor ID for component averages:', this.userID);
        return true;
      }
      return false;
    },
    async fetchAdvisees() {
      try {
        const res = await fetch(`http://localhost:3000/advisor/advisees?advisor_id=${this.userID}`);
        if (!res.ok) throw new Error('Failed to fetch advisees');
        this.advisees = await res.json();
      } catch (error) {
        console.error('Error fetching advisees:', error);
        this.errorMessage = 'Failed to load advisees.';
      }
    },
    async fetchStudentEnrollments() {
      if (!this.selectedAdviseeId) return;
      try {
        const res = await fetch(`http://localhost:3000/student/enrollments?student_id=${this.selectedAdviseeId}`);
        if (!res.ok) throw new Error('Failed to fetch enrollments');
        this.studentEnrollments = await res.json();
        this.selectedCourseId = '';
        this.selectedSectionId = '';
        this.averages = [];
        this.adviseeMarks = [];
      } catch (error) {
        console.error('Error fetching enrollments:', error);
        this.errorMessage = 'Failed to load enrollments.';
      }
    },
    async onAdviseeChange() {
      await this.fetchStudentEnrollments();
    },
    async onCourseChange() {
      this.selectedSectionId = '';
      this.averages = [];
      this.adviseeMarks = [];
    },
    async fetchAveragesAndMarks() {
      if (!this.selectedAdviseeId || !this.selectedCourseId || !this.selectedSectionId) return;
      await this.fetchAverages();
      await this.fetchAdviseeMarks();
    },
    async fetchAverages() {
      try {
        this.averages = [];
        const url = `http://localhost:3000/class/component-averages?course_id=${this.selectedCourseId}&section_id=${this.selectedSectionId}`;
        const res = await fetch(url);
        if (!res.ok) {
          this.errorMessage = 'Failed to load averages (server error).';
          return;
        }
        this.averages = await res.json();
      } catch (err) {
        this.errorMessage = 'Failed to load averages (network error).';
      }
    },
    async fetchAdviseeMarks() {
      if (!this.selectedAdviseeId || !this.selectedCourseId || !this.selectedSectionId) return;
      try {
        const url = `http://localhost:3000/student/marks?student_id=${this.selectedAdviseeId}&course_id=${this.selectedCourseId}&section_id=${this.selectedSectionId}`;
        const res = await fetch(url);
        if (!res.ok) throw new Error('Failed to fetch advisee marks');
        const data = await res.json();
        this.adviseeMarks = Array.isArray(data.marks) ? data.marks : [];
      } catch (error) {
        console.error('Error fetching advisee marks:', error);
        this.adviseeMarks = [];
      }
    },
    getAdviseeMark(component_id) {
      const found = this.adviseeMarks.find(m => m.component_id == component_id);
      return found ? found.mark : '-';
    },
    compareToAverage(comp) {
      const mark = Number(this.getAdviseeMark(comp.component_id));
      const avg = Number(comp.average_mark);
      if (isNaN(mark) || isNaN(avg)) return '-';
      if (mark > avg) return 'Above Average';
      if (mark < avg) return 'Below Average';
      return 'At Average';
    },
  },
  async mounted() {
    if (this.getAuthenticatedUser()) {
      await this.fetchAdvisees();
    } else {
      this.errorMessage = 'Authentication required. Please login.';
      this.$router.push('/login');
    }
  }
};
</script>

<style scoped>
.advisor-component-averages {
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
.above-average {
  color: green;
  font-weight: bold;
}
.below-average {
  color: red;
  font-weight: bold;
}
.at-average {
  color: orange;
  font-weight: bold;
}
</style> 