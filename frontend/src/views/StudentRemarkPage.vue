<template>
  <div class="student-remark">
    <h1>Submit Remark Request</h1>
    <form @submit.prevent="submitRequest">
      <div class="form-group">
        <label for="course">Course:</label>
        <select v-model="courseId" @change="fetchSections" required>
          <option disabled value="">-- Select Course --</option>
          <option v-for="course in courses" :key="course.course_id" :value="course.course_id">
            {{ course.course_name }}
          </option>
        </select>
      </div>
      <div class="form-group" v-if="sections.length">
        <label for="section">Section:</label>
        <select v-model="sectionId" @change="fetchComponents" required>
          <option disabled value="">-- Select Section --</option>
          <option v-for="section in sections" :key="section.section_id" :value="section.section_id">
            Section {{ section.section_number }}
          </option>
        </select>
      </div>
      <div class="form-group" v-if="components.length">
        <label for="component">Component (optional):</label>
        <select v-model="componentId">
          <option value="">-- All / Not Specific --</option>
          <option v-for="comp in components" :key="comp.component_id" :value="comp.component_id">
            {{ comp.component_name }}
          </option>
        </select>
      </div>
      <div class="form-group">
        <label for="justification">Justification:</label>
        <textarea v-model="justification" required rows="3" placeholder="Explain why you are requesting a remark..."></textarea>
      </div>
      <button type="submit">Submit Request</button>
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
        <tr v-for="req in requests" :key="req.request_id">
          <td>{{ new Date(req.created_at).toLocaleString() }}</td>
          <td>{{ req.course_id }}</td>
          <td>{{ req.section_id }}</td>
          <td>{{ req.component_name || '-' }}</td>
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
    return {
      studentID: 1,
      courses: [],
      sections: [],
      components: [],
      courseId: '',
      sectionId: '',
      componentId: '',
      justification: '',
      successMessage: '',
      errorMessage: '',
      requests: []
    };
  },
  methods: {
    async fetchCourses() {
      try {
        const res = await fetch('http://localhost:3000/courses');
        this.courses = await res.json();
      } catch (err) {
        this.errorMessage = 'Failed to load courses.';
      }
    },
    async fetchSections() {
      try {
        this.sections = [];
        this.components = [];
        const res = await fetch(`http://localhost:3000/sections?course_id=${this.courseId}`);
        this.sections = await res.json();
      } catch {
        this.errorMessage = 'Failed to load sections.';
      }
    },
    async fetchComponents() {
      try {
        this.components = [];
        const res = await fetch(`http://localhost:3000/components?course_id=${this.courseId}&section_id=${this.sectionId}`);
        this.components = await res.json();
      } catch {
        this.errorMessage = 'Failed to load components.';
      }
    },
    async submitRequest() {
      try {
        const payload = {
          student_id: this.studentID,
          course_id: this.courseId,
          section_id: this.sectionId,
          component_id: this.componentId || null,
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
        const res = await fetch(`http://localhost:3000/student/remark-requests?student_id=${this.studentID}`);
        this.requests = await res.json();
      } catch {
        this.requests = [];
      }
    }
  },
  mounted() {
    this.fetchCourses();
    this.fetchRequests();
  }
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