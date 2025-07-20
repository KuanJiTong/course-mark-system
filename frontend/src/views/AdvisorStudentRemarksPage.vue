<template>
  <div>
    <h2 class="mt-4 mb-4">Student Feedback / Remark Requests</h2>
    <table v-if="remarks.length">
      <thead>
        <tr>
          <th>Date</th>
          <th>Advisee</th>
          <th>Course Code</th>
          <th>Course Name</th>
          <th>Section</th>
          <th>Component</th>
          <th>Justification</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="remark in remarks" :key="remark.request_id">
          <td>{{ new Date(remark.created_at).toLocaleString() }}</td>
          <td>{{ getAdviseeName(remark.student_id) }}</td>
          <td>{{ remark.course_code }}</td>
          <td>{{ remark.course_name }}</td>
          <td>{{ remark.section_number }}</td>
          <td>{{ remark.component_name || '-' }}</td>
          <td>{{ remark.justification }}</td>
          <td>{{ remark.status }}</td>
        </tr>
      </tbody>
    </table>
    <div v-if="!remarks.length && loaded">No feedback/remarks found for your advisees.</div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      userID: null, // Will be set from sessionStorage
      remarks: [],
      advisees: [],
      loaded: false,
    };
  },
  methods: {
    // Get authenticated user data
    getAuthenticatedUser() {
      const userData = sessionStorage.getItem('user');
      if (userData) {
        const user = JSON.parse(userData);
        this.userID = user.lecturerId; // Use lecturerId as advisor_id
        console.log('Authenticated advisor (lecturer) ID for student remarks:', this.userID);
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
      }
    },
    async fetchRemarks() {
      this.remarks = [];
      this.loaded = false;
      try {
        // Fetch all remarks for all advisees
        const allRemarks = [];
        for (const advisee of this.advisees) {
          let url = `http://localhost:3000/student/remark-requests?student_id=${advisee.student_id}`;
          const res = await fetch(url);
          if (!res.ok) continue;
          const data = await res.json();
          for (const remark of data) {
            allRemarks.push({
              created_at: remark.createdAt,
              request_id: remark.requestId,
              justification: remark.justification,
              status: remark.status,
              component_name: remark.componentName,
              section_number: remark.sectionNumber,
              course_code: remark.courseCode,
              course_name: remark.courseName,
              student_id: advisee.student_id
            });
          }
        }
        this.remarks = allRemarks.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        this.loaded = true;
      } catch (error) {
        console.error('Error fetching remarks:', error);
        this.loaded = true;
      }
    },
    getAdviseeName(student_id) {
      const found = this.advisees.find(a => a.student_id == student_id);
      return found ? found.student_name : 'Advisee';
    }
  },
  async mounted() {
    if (this.getAuthenticatedUser()) {
      await this.fetchAdvisees();
      await this.fetchRemarks();
    } else {
      console.error('Authentication required. Please login.');
      this.$router.push('/login');
    }
  }
};
</script>

<style scoped>
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
</style> 