<template>
  <button class="mt-4 btn btn-secondary mb-4" @click="goBack">Back</button>
  <h2 class="mb-4">Student Enrollment Management</h2>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2 flex-wrap">
      <button @click="openAddStudentModal" class="mb-2 btn btn-primary">
        Enroll Student
      </button>
      <input
        v-model="searchQuery"
        @input = "searchStudent"
        type="text"
        class="form-control"
        placeholder="Search students..."
      />
    </div>
  </div>

  <div class="table-responsive shadow-sm rounded">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th style="width: 50px;">#</th>
          <th style="width: 100px;">Matric No.</th>
          <th style="width: 250px;">Student Name</th>
          <th style="width: 170px;">Email</th>
          <th style="width: 200px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="students.length === 0 && searchQuery" class="text-center text-muted">
          <td colspan="7">No students found</td>
        </tr>
        <tr v-else-if="students.length === 0" class="text-center text-muted">
          <td colspan="7">No students added yet.</td>
        </tr>
        <tr v-for="(student, index) in students" :key="index">
          <td>{{ index + 1 }}</td>
          <td>
            <span>{{ student.matricNo}}</span>
          </td>
          <td>
            <span>{{ student.studentName }}</span>
          </td>
          <td>
            <span>{{ student.email }}</span>
          </td>
          <td class="text-center">
            <div class="icon-row">
              <i class="bi bi bi-trash-fill text-danger mx-2" data-bs-toggle="tooltip" title="Remove Student" @click.stop="deleteStudent(student.enrollmentId)"></i>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <StudentEnrollModal
    :show="showModal"
    :sectionId="sectionId"
    @close="closeModal"
    @submit-enrollment="enrollStudent"
  />
</template>

<script>
import StudentEnrollModal from '../components/StudentEnrollModal.vue'; 

export default {
  components:{ StudentEnrollModal },
  data() {
    return {
      lecturerID: null,
      sectionId: null,
      students: [],
      searchQuery: "",
      showModal: false
    };
  },
  methods: {
    goBack() {
      this.$router.back();
    },
    getAuthenticatedUser() {
      const userData = sessionStorage.getItem('user');
      if (userData) {
        const user = JSON.parse(userData);
        this.lecturerID = user.user_id;
        console.log('Authenticated lecturer ID for enrollment management:', this.lecturerID);
        return true;
      }
      return false;
    },
    async fetchAllStudents(){
      try {
        const sectionId = this.sectionId; 
        const url = `http://localhost:3000/student-enrollment/${sectionId}`;

        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch students');

        const data = await response.json();
        this.students = data; 
      } catch (error) {
        console.error('Error fetching students:', error);
      }
    },
    async searchStudent() {
      try {
        const sectionId = this.sectionId;

        const trimmedKeyword = this.searchQuery.trim();
        if (trimmedKeyword === '') {
          await this.fetchAllStudents();
          return;
        }

        const url = `http://localhost:3000/student-enrollment/${sectionId}?keyword=${encodeURIComponent(trimmedKeyword)}`;
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch students');

        const data = await response.json();
        this.students = data;
      } catch (error) {
        console.error('Error searching students:', error);
      }
    },
    openAddStudentModal() {
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    async enrollStudent(enrollList) {
      const enrollment = {
        sectionId: this.sectionId,
        enrollList: enrollList
      }
      // Send POST request
      await fetch('http://localhost:3000/student-enrollment', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(enrollment)
      })
      .then(async response => {
        if (!response.ok) {
          throw new Error('Failed to add enrollment');
        }
        return await response.json();
      })
      .then(data => {
        alert('Enrollment added:', data);

        // Fetch the updated student list
        this.fetchAllStudents();
        this.closeModal();
      })
      .catch(error => {
        console.error('Error:', error);
      });
    },
    async deleteStudent(enrollmentId) {
      if (!confirm('Are you sure you want to remove this student?')) return;

      try {
        const response = await fetch(`http://localhost:3000/student-enrollment/${enrollmentId}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
          },
        });

        const result = await response.json();

        if (response.ok) {
          alert(result.message || 'Student removed successfully.');
          await this.fetchAllStudents();
        } else {
          alert(result.error || 'Failed to remove student.');
        }
      } catch (error) {
        console.error('Delete error:', error);
        alert('An error occurred while deleting.');
      }
    }
  },
  async created(){
    this.$emit('update-active-tab', 'My Courses');
    if (this.getAuthenticatedUser()) {
      this.sectionId = this.$route.params.sectionId;
      await this.fetchAllStudents();
    } else {
      console.error('Authentication required. Please login.');
      this.$router.push('/login');
    }
  },
};
</script>

<style scoped>
.table-responsive {
  background-color: #fff;
}

.icon-row {
  display: flex;
  justify-content: center;
  gap: 3px; 
}

.bi{
  cursor: pointer;
  width: 25px;
  height: 25px;
  border-radius: 50%;
  background-color: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s ease;
}

.bi:hover {
  background-color: #b5b3b368; 
}

.bi:active {
  transform: scale(0.9); 
  background-color: #e0e0e0; 
}
</style>
