<template>
  <div class="advisor-compare">
    <h1>Compare Advisees with Coursemates</h1>
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
      <select v-model="selectedSectionId" @change="fetchMarks" required>
        <option disabled value="">-- Select Section --</option>
        <option v-for="section in filteredSections" :key="section.section_id" :value="section.section_id">
          Section {{ section.section_number }}
        </option>
      </select>
    </div>
    <div v-if="marks.length">
      <div style="margin-bottom: 32px;">
        <Bar :data="barChartData" :options="barChartOptions" />
      </div>
      <table>
        <thead>
          <tr>
            <th>Student</th>
            <th>Coursework (70%)</th>
            <th>Final Exam (30%)</th>
            <th>Total Mark</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in marks" :key="item.student_id" :class="{ 'highlight': item.student_id == selectedAdviseeId }">
            <td>
              <span v-if="item.student_id == selectedAdviseeId">
                {{ item.student_name }} (Advisee)
              </span>
              <span v-else>
                {{ item.student_name }}
              </span>
            </td>
            <td>{{ item.coursework_mark }}</td>
            <td>{{ item.final_exam_mark }}</td>
            <td>{{ calculateTotal(item.coursework_mark, item.final_exam_mark) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>

<script>
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

export default {
  components: { Bar },
  data() {
    return {
      userID: null, // Will be set from sessionStorage
      advisees: [],
      selectedAdviseeId: '',
      studentEnrollments: [],
      selectedCourseId: '',
      selectedSectionId: '',
      marks: [],
      errorMessage: ''
    };
  },
  computed: {
    uniqueCourses() {
      // Get unique courses from studentEnrollments
      const map = {};
      this.studentEnrollments.forEach(e => { map[e.course_id] = e; });
      return Object.values(map);
    },
    filteredSections() {
      return this.studentEnrollments.filter(e => e.course_id == this.selectedCourseId);
    },
    barChartData() {
      if (!this.marks.length) return { labels: [], datasets: [] };
      return {
        labels: this.marks.map(item =>
          item.student_id == this.selectedAdviseeId ? (item.student_name + ' (Advisee)') : item.student_name
        ),
        datasets: [
          {
            label: 'Total Mark',
            backgroundColor: this.marks.map(item =>
              item.student_id == this.selectedAdviseeId ? '#ffc107' : '#0d6efd'
            ),
            data: this.marks.map(item => Number(this.calculateTotal(item.coursework_mark, item.final_exam_mark)))
          }
        ]
      };
    },
    barChartOptions() {
      return {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: { display: true, text: 'Comparison: Total Marks' }
        },
        scales: {
          y: { beginAtZero: true, max: 100 }
        }
      };
    }
  },
  methods: {
    // Get authenticated user data
    getAuthenticatedUser() {
      const userData = sessionStorage.getItem('user');
      if (userData) {
        const user = JSON.parse(userData);
        this.userID = user.user_id;
        console.log('Authenticated advisor ID for comparison:', this.userID);
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
        this.marks = [];
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
      this.marks = [];
    },
    async fetchMarks() {
      if (!this.selectedAdviseeId || !this.selectedCourseId || !this.selectedSectionId) return;
      try {
        this.marks = [];
        const url = `http://localhost:3000/advisor/section-marks?advisor_id=${this.userID}&course_id=${this.selectedCourseId}&section_id=${this.selectedSectionId}`;
        const res = await fetch(url);
        if (!res.ok) {
          this.errorMessage = 'Failed to load marks (server error).';
          return;
        }
        this.marks = await res.json();
      } catch (err) {
        this.errorMessage = 'Failed to load marks (network error).';
      }
    },
    calculateTotal(coursework, finalExam) {
      const cw = Number(coursework) || 0;
      const fe = Number(finalExam) || 0;
      return (cw + fe).toFixed(2);
    }
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
.advisor-compare {
  max-width: 800px;
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
.highlight {
  background-color: #fff3cd !important;
  font-weight: bold;
}
</style> 