<template>
  <div class="student-remark">
    <h1>Submit Remark Request</h1>
    <form @submit.prevent="submitRequest" class="p-4 border rounded bg-light">
      <div class="mb-3">
        <label for="course" class="form-label">Course:</label>
        <select class="form-select" v-model="sectionId" @change="fetchComponents" required>
          <option disabled value="">-- Select Course --</option>
          <option v-for="course in courses" :key="course.sectionId" :value="course.sectionId">
            {{ course.courseCode }}-{{ course.sectionNumber }} {{ course.courseName }}
          </option>
        </select>
      </div>

      <div class="mb-3" v-if="components.length">
        <label for="component" class="form-label">Component (optional):</label>
        <select class="form-select" v-model="componentId">
          <option disabled value="">-- All / Not Specific --</option>
          <option v-for="comp in components" :key="comp.componentId" :value="comp.componentId">
            {{ comp.componentName }}
          </option>
          <!-- <option value="finalExam">Final Exam</option> -->
        </select>
      </div>

      <div class="mb-3">
        <label for="justification" class="form-label">Justification:</label>
        <textarea
          v-model="justification"
          required
          class="form-control"
          rows="3"
          placeholder="Explain why you are requesting a remark...">
        </textarea>
      </div>

      <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>

    <div v-if="successMessage" class="success-message">{{ successMessage }}</div>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>

    <h2 style="margin-top:2rem;">My Previous Remark Requests</h2>
    <table v-if="requests.length">
      <thead>
        <tr>
          <th>Date</th>
          <th>Course</th>
          <th>Section</th>
          <th>Component</th>
          <th>Justification</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="req in requests" :key="req.requestId">
          <td>{{ new Date(req.createdAt).toLocaleString() }}</td>
          <td>{{ req.courseCode }} {{ req.courseName }}</td>
          <td>{{ req.sectionNumber }}</td>
          <td>{{ req.componentName || '-' }}</td>
          <td>{{ req.justification }}</td>
          <td>{{ req.status }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  data() {
    const user = JSON.parse(sessionStorage.getItem('user'));
    return {
      studentId: user.studentId,
      courses: [],
      components: [],
      sectionId: '',
      componentId: '',
      justification: '',
      successMessage: '',
      errorMessage: '',
      requests: []
    };
  },
 async created(){
    await this.fetchCourses();
    await this.fetchRequests();
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
          this.sectionId = this.courses[0].sectionId;
          await this.fetchComponents();
        }
      } catch (err) {
        this.errorMessage = 'Failed to load courses.';
      }
    },
    async fetchComponents() {
      try {
        this.components = [];
        const res = await fetch(`http://localhost:3000/components?section_id=${this.sectionId}`);
        this.components = await res.json();
      } catch {
        this.errorMessage = 'Failed to load components.';
      }
    },
    async submitRequest() {
      try {
        const payload = {
          studentId: this.studentId,
          sectionId: this.sectionId,
          componentId: this.componentId || null,
          justification: this.justification
        };
        const res = await fetch('http://localhost:3000/student/remark-request', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (res.ok) {
          this.successMessage = data.message;
          this.errorMessage = '';
          this.justification = '';
          this.componentId = '';
          this.fetchRequests();
        } else {
          this.errorMessage = data.error || 'Failed to submit request.';
          this.successMessage = '';
        }
      } catch (err) {
        this.errorMessage = 'Failed to submit request (network error).';
        this.successMessage = '';
      }
    },
    async fetchRequests() {
      try {
        const res = await fetch(`http://localhost:3000/student/remark-requests?student_id=${this.studentId}`);
        this.requests = await res.json();
      } catch {
        this.requests = [];
      }
    }
  },
};
</script>

<style scoped>
.student-remark {
  max-width: 700px;
  margin: auto;
  padding: 20px;
}
form {
  margin-bottom: 2rem;
}
.form-group {
  margin-bottom: 15px;
}
label {
  font-weight: bold;
}
select, textarea {
  width: 100%;
  padding: 8px;
  font-size: 16px;
  margin-top: 5px;
}
button {
  background: #0d6efd;
  color: #fff;
  border: none;
  padding: 10px 24px;
  border-radius: 5px;
  font-size: 16px;
  cursor: pointer;
}
button:hover {
  background: #084298;
}
.success-message {
  color: green;
  margin-bottom: 10px;
}
.error-message {
  color: red;
  margin-bottom: 10px;
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
</style> 