<template>
  <div class="advisor-compare">
    <h1>Compare Advisees with Coursemates</h1>
    <div class="form-group">
      <label for="advisee">Advisee:</label>
      <select v-model="selectedAdviseeId" @change="onAdviseeChange" required class="form-select">
        <option disabled value="">-- Select Advisee --</option>
        <option v-for="advisee in advisees" :key="advisee.student_id" :value="advisee.student_id">
          {{ advisee.student_name }} ({{ advisee.matric_no }})
        </option>
      </select>
    </div>
    <div class="form-group" v-if="studentEnrollments.length">
      <label for="course">Course:</label>
      <select v-model="selectedCourseId" @change="onCourseChange" required class="form-select">
        <option disabled value="">-- Select Course --</option>
        <option v-for="course in uniqueCourses" :key="course.course_id" :value="course.course_id">
          {{ course.course_code }}-{{ (filteredSections.find(s => s.course_id === course.course_id)?.section_number || '') }} {{ course.course_name }}
        </option>
      </select>
    </div>
    <div v-if="marks.length">
      <div v-if="selectedComponent === 'finalExam' && components.length" style="margin-bottom: 8px;">
        <strong>Final Exam Max Mark:</strong> {{ getFinalExamMax() }}
      </div>
      <div class="form-group" v-if="components.length">
        <label for="component">Component:</label>
        <select class="form-select" v-model="selectedComponent">
          <option disabled value="">-- Select Component --</option>
          <option value="total">Total Mark</option>
          <option v-for="c in components" :key="c.componentName" :value="c.componentName">{{ c.componentName }}</option>
          <option value="finalExam">Final Exam</option>
        </select>
      </div>

      <div style="margin-bottom: 32px;">
        <Bar :data="barChartData" :options="barChartOptions" />
      </div>
      <table>
        <thead>
          <tr>
            <th>Student</th>
            <th v-if="selectedComponent === 'total'">Total Mark</th>
            <th v-else-if="selectedComponent === 'finalExam'">Final Exam</th>
            <th v-else>{{ selectedComponent }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in marks" :key="item.student_id" :class="{ 'highlight': item.is_advisee, 'at-risk': atRiskStudentIds.includes(item.student_id) && selectedComponent === 'total' }">
            <td>
              <span v-if="item.is_advisee">
                {{ item.student_name }} (Advisee)
              </span>
              <span v-else>
                {{ item.student_name }}
              </span>
            </td>
            <td v-if="selectedComponent === 'total'">{{ item.total_mark }}</td>
            <td v-else-if="selectedComponent === 'finalExam'">{{ item.final_exam_mark }}</td>
            <td v-else>{{ getComponentMark(item, selectedComponent) }}</td>
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
      components: [],
      selectedComponent: 'total',
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
    atRiskStudentIds() {
      // Only for total mark view
      if (this.selectedComponent !== 'total' || !this.marks.length) return [];
      // Calculate bottom 20%
      const sorted = [...this.marks].sort((a, b) => a.total_mark - b.total_mark);
      const cutoff = Math.ceil(this.marks.length * 0.2);
      const bottom20 = sorted.slice(0, cutoff).map(s => s.student_id);
      // GPA < 2.0 (assuming total_mark out of 100, GPA = total_mark/25)
      const lowGPA = this.marks.filter(s => (s.total_mark / 25) < 2.0).map(s => s.student_id);
      // Union of both
      return Array.from(new Set([...bottom20, ...lowGPA]));
    },
    barChartData() {
      if (!this.marks.length) return { labels: [], datasets: [] };
      let label = 'Total Mark';
      let data = [];
      if (this.selectedComponent === 'total') {
        data = this.marks.map(item => Number(item.total_mark));
        label = 'Total Mark';
      } else if (this.selectedComponent === 'finalExam') {
        data = this.marks.map(item => Number(item.final_exam_mark));
        label = 'Final Exam';
      } else {
        data = this.marks.map(item => Number(this.getComponentMark(item, this.selectedComponent)));
        label = this.selectedComponent;
      }
      return {
        labels: this.marks.map(item =>
          item.is_advisee ? (item.student_name + ' (Advisee)') : item.student_name
        ),
        datasets: [
          {
            label,
            backgroundColor: this.marks.map(item =>
              item.is_advisee ? '#ffc107' : '#0d6efd'
            ),
            data
          }
        ]
      };
    },
    barChartOptions() {
      return {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: {
            display: true,
            text:
              this.selectedComponent === 'total'
                ? 'Anonymous Comparison: Total Marks'
                : this.selectedComponent === 'finalExam'
                  ? 'Anonymous Comparison: Final Exam'
                  : `Anonymous Comparison: ${this.selectedComponent}`
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: this.getChartMax()
          }
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
        this.userID = user.lecturerId; // Use lecturerId as advisor_id
        console.log('Authenticated advisor (lecturer) ID for comparison:', this.userID);
        return true;
      }
      return false;
    },
    async fetchAdvisees() {
      try {
        const res = await fetch(`http://localhost:3000/advisor/advisees?advisor_id=${this.userID}`);
        if (!res.ok) throw new Error('Failed to fetch advisees');
        this.advisees = await res.json();
        console.log('Fetched advisees:', this.advisees);
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
      // Auto-select the first section for the selected course
      const sections = this.studentEnrollments.filter(e => e.course_id == this.selectedCourseId);
      if (sections.length) {
        this.selectedSectionId = sections[0].section_id;
        await this.fetchMarks();
      } else {
        this.selectedSectionId = '';
        this.marks = [];
      }
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
        const data = await res.json();
        // Map backend keys to frontend keys for consistency
        this.marks = (data || []).map(item => ({
          student_id: item.student_id,
          student_name: item.student_name,
          coursework_mark: item.coursework_mark,
          final_exam_mark: item.final_exam_mark,
          total_mark: item.total_mark,
          is_advisee: item.is_advisee,
          marks: item.marks || {},
        }));
        // Fetch components for the selected section
        await this.fetchComponents();
      } catch (err) {
        this.errorMessage = 'Failed to load marks (network error).';
      }
    },
    async fetchComponents() {
      // Fetch components for the selected section
      if (!this.selectedSectionId) {
        this.components = [];
        return;
      }
      try {
        const res = await fetch(`http://localhost:3000/components?section_id=${this.selectedSectionId}`);
        if (!res.ok) {
          this.components = [];
          return;
        }
        this.components = await res.json();
      } catch (err) {
        this.components = [];
      }
    },
    getComponentMark(item, componentName) {
      // item.marks is an object: {componentName: mark}
      return item.marks && item.marks[componentName] !== undefined ? item.marks[componentName] : 0;
    },
    calculateTotal(coursework, finalExam) {
      const cw = Number(coursework) || 0;
      const fe = Number(finalExam) || 0;
      return (cw + fe).toFixed(2);
    },
    getChartMax() {
      if (this.selectedComponent === 'total') return 100;
      if (this.selectedComponent === 'finalExam') return this.getFinalExamMax();
      // For component, find maxMark from components
      const comp = this.components.find(c => c.componentName === this.selectedComponent);
      return comp ? parseFloat(comp.maxMark) : 100;
    },
    getFinalExamMax() {
      // Try to find the max mark for the final exam component
      const comp = this.components.find(c => c.componentName === 'Final Exam');
      if (comp && comp.maxMark) return parseFloat(comp.maxMark);
      // Fallback: use the largest maxMark in components
      const max = Math.max(...this.components.map(c => parseFloat(c.maxMark) || 0));
      return max > 0 ? max : 100;
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
.at-risk {
  background-color: #ffd6d6 !important;
  color: #b30000;
  font-weight: bold;
}
</style> 