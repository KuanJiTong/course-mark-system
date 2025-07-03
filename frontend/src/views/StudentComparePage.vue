<template>
  <div class="student-compare">
    <h1>Compare with Coursemates</h1>
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
    <div v-if="marks.length" style="margin-bottom: 32px;">
      <Bar :data="barChartData" :options="barChartOptions" />
    </div>
    <table v-if="marks.length">
      <thead>
        <tr>
          <th>Student</th>
          <th>Coursework (70%)</th>
          <th>Final Exam (30%)</th>
          <th>Total Mark</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in marks" :key="item.student_id" :class="{ 'highlight': item.student_id == currentStudentId }">
          <td>
            <span v-if="item.student_id == currentStudentId">You</span>
            <span v-else>Classmate</span>
          </td>
          <td>{{ calculateCoursework(item.marks) }}</td>
          <td>{{ item.final_exam_mark }}</td>
          <td>{{ item.total }}</td>
        </tr>
      </tbody>
    </table>
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
      studentID: null,
      courses: [],
      sections: [],
      marks: [],
      selectedCourseId: '',
      selectedSectionId: '',
      comparisonData: [],
      errorMessage: ''
    };
  },
  computed: {
    currentStudentId() {
      return this.studentID;
    },
    barChartData() {
      if (!this.marks.length) return { labels: [], datasets: [] };
      return {
        labels: this.marks.map(item => item.student_id == this.currentStudentId ? 'You' : 'Classmate'),
        datasets: [
          {
            label: 'Total Mark',
            backgroundColor: this.marks.map(item => item.student_id == this.currentStudentId ? '#ffc107' : '#0d6efd'),
            data: this.marks.map(item => Number(item.total))
          }
        ]
      };
    },
    barChartOptions() {
      return {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: { display: true, text: 'Anonymous Comparison: Total Marks' }
        },
        scales: {
          y: { beginAtZero: true, max: 100 }
        }
      };
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
    async fetchMarks() {
      try {
        this.marks = [];
        const url = `http://localhost:3000/all_marks?course_id=${this.selectedCourseId}&section_id=${this.selectedSectionId}`;
        const res = await fetch(url);
        if (!res.ok) {
          this.errorMessage = 'Failed to load marks (server error).';
          return;
        }
        const result = await res.json();
        this.marks = result.data || [];
      } catch (err) {
        this.errorMessage = 'Failed to load marks (network error).';
      }
    },
    calculateCoursework(marksObj) {
      // Sum all component marks (object values)
      return Object.values(marksObj || {}).reduce((sum, val) => sum + Number(val), 0);
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
.student-compare {
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