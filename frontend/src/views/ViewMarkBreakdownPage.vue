<template>
  <div class="mark-breakdown">
    <h1>View Full Mark Breakdown</h1>

    <div class="form-group">
      <label for="course">Course:</label>
      <select class="form-select" v-model="selectedSectionId" @change="fetchMarks" required>
        <option disabled value="">-- Select Course --</option>
        <option v-for="course in courses" :key="course.sectionId" :value="course.sectionId">
          {{ course.courseCode }}-{{ course.sectionNumber }} {{ course.courseName }}
        </option>
      </select>
    </div>

    <!-- Table Display -->
    <table v-if="marks.length">
      <thead>
        <tr>
          <th>No.</th>
          <th>Student</th>
          <th>Matric No.</th>
          <th v-for="comp in components" :key="comp.componentId">{{ comp.componentName }}</th>
          <th>Final Exam</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(student,index) in marks"
          :key="student.studentId"
          @click="selectedStudentId = student.studentId"
          :class="{ 'selected-row': selectedStudentId === student.studentId }"
          style="cursor: pointer;"
        >
          <td>{{ index + 1 }}</td>
          <td>{{ student.studentName }}</td>
          <td>{{ student.matricNo }}</td>
          <td v-for="comp in components" :key="comp.componentId">
            {{ formatMark(student.marks[comp.componentName]) }}
          </td>
          <td>{{ formatMark(student.finalExamMark) }}</td>
          <td>{{ student.total.toFixed(2) }}</td>
        </tr>
      </tbody>
    </table>

    <!-- Export CSV Button -->
    <button v-if="marks.length" @click="exportCSV" class="btn btn-primary mt-2">
      Export Marks as CSV
    </button>

    <!-- Student Performance Charts (only selected student) -->
    <div class="student-charts" v-if="selectedStudent">
      <StudentProgressChart
        :key="selectedStudent.studentId + '-chart'"
        :studentName="selectedStudent.studentName"
        :components="[...components.map(c => c.componentName), 'Final Exam']"
        :marks="{ ...selectedStudent.marks, 'Final Exam': selectedStudent.finalExamMark }"
      />

      <div class="student-pie-charts">
        <StudentPieChart
          :key="selectedStudent.studentId + '-pie'"
          :studentName="selectedStudent.studentName"
          :components="[...components.map(c => c.componentName), 'Final Exam']"
          :marks="{ ...selectedStudent.marks, 'Final Exam': selectedStudent.finalExamMark }"
        />
      </div>
    </div>

    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>

<script>
import StudentProgressChart from '@/components/StudentProgressChart.vue';
import StudentPieChart from '@/components/StudentPieChart.vue';

export default {
  components: {
    StudentProgressChart,
    StudentPieChart
  },
  data() {
    const user = JSON.parse(sessionStorage.getItem('user'));
    return {
      lecturerId: user?.lecturerId || '',
      courses: [],
      sections: [],
      marks: [],
      components: [],
      selectedSectionId: '',
      errorMessage: '',
      selectedStudentId: null
    };
  },
  async created() {
    await this.fetchAllLecturerCourses();
  },
  computed: {
    selectedCourse() {
      return this.courses.find(c => c.course_id === this.selectedCourseId) || null;
    },
    selectedStudent() {
      return this.marks.find(m => m.studentId === this.selectedStudentId) || null;
    },
    maxCourseworkMark() {
      return this.selectedCourse?.max_cm || 0;
    },
    maxFinalExamMark() {
      return this.selectedCourse?.max_fm || 0;
    },
    courseworkWeight() {
      const total = this.maxCourseworkMark + this.maxFinalExamMark;
      return total > 0 ? ((this.maxCourseworkMark / total) * 100).toFixed(0) : 0;
    },
    finalExamWeight() {
      const total = this.maxCourseworkMark + this.maxFinalExamMark;
      return total > 0 ? ((this.maxFinalExamMark / total) * 100).toFixed(0) : 0;
    }
  },
  methods: {
    async fetchAllLecturerCourses() {
      try {
        if (!this.lecturerId) {
          console.error('No lecturer ID available');
          return;
        }

        const url = `http://localhost:3000/lecturer-course/${this.lecturerId}`;
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch courses');

        const data = await response.json();
        this.courses = data;

        if (this.courses.length) {
          if (!this.selectedSectionId) {
            this.selectedSectionId = this.courses[0].sectionId;
          }
          await this.fetchMarks();
        }
      } catch (error) {
        console.error('Error fetching courses:', error);
      }
    },
    async fetchMarks() {
      try {
        this.marks = [];
        this.selectedStudentId = null;
        const url = `http://localhost:3000/all_marks?section_id=${this.selectedSectionId}`;
        const res = await fetch(url);
        if (!res.ok) {
          this.errorMessage = 'Failed to load marks (server error).';
          return;
        }
        const result = await res.json();
        this.components = result.components || [];
        this.marks = result.data || [];
      } catch (err) {
        this.errorMessage = 'Failed to load marks (network error).';
      }
    },
    exportCSV() {
      const selectedCourse = this.courses.find(c => c.sectionId === this.selectedSectionId);
      if (!selectedCourse) {
        alert("Please select a valid course.");
        return;
      }

      const url = `http://localhost:3000/all_marks_csv?section_id=${this.selectedSectionId}`;
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `marks_${selectedCourse.courseCode}_${selectedCourse.sectionNumber}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
    formatMark(mark) {
      const num = Number(mark);
      return isNaN(num) ? '0.00' : num.toFixed(2);
    }
  }
};
</script>

<style scoped>
.mark-breakdown {
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
tr:hover {
  background-color: #f0f8ff;
}
.selected-row {
  background-color: #e0f7fa !important;
}
.error-message {
  color: red;
  margin-top: 10px;
}
.student-charts {
  margin-top: 30px;
  display: flex;
  flex-direction: column;
  gap: 25px;
}
.student-pie-charts {
  display: flex;
  flex-wrap: wrap;
  gap: 30px;
  justify-content: center;
}
</style>
