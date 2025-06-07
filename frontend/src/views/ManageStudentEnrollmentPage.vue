<template>
  <div class="login-container">
    <div class="login-box">
      <div class="login-content">
        <h2>Manage Student Enrollment</h2>

        <!-- Form -->
        <div class="mb-3">
          <label class="form-label"><b>Student ID</b></label>
          <div class="form-input">
            <i class="bi bi-person"></i>
            <input v-model="newStudent.id" type="text" class="form-control" placeholder="Enter Student ID" />
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label"><b>Student Name</b></label>
          <div class="form-input">
            <i class="bi bi-person-badge"></i>
            <input v-model="newStudent.name" type="text" class="form-control" placeholder="Enter Student Name" />
          </div>
        </div>

        <button @click="addStudent">Add Student</button>

        <!-- Table -->
        <div v-if="students.length" class="mt-4">
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Student ID</th>
                <th>Name</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(student, index) in students" :key="student.id">
                <td>{{ index + 1 }}</td>
                <td>{{ student.id }}</td>
                <td>{{ student.name }}</td>
                <td>
                  <button class="btn-edit" @click="editStudent(student)">Edit</button>
                  <button class="btn-delete" @click="removeStudent(student.id)">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Edit Form -->
        <div v-if="editingStudent" class="mt-4">
          <h3>Edit Student</h3>
          <div class="mb-3">
            <label class="form-label"><b>Student ID</b></label>
            <input v-model="editingStudent.id" type="text" class="form-control" />
          </div>
          <div class="mb-3">
            <label class="form-label"><b>Student Name</b></label>
            <input v-model="editingStudent.name" type="text" class="form-control" />
          </div>
          <button @click="updateStudent">Update</button>
          <button class="btn-cancel" @click="cancelEdit">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  data() {
    return {
      students: [],
      newStudent: { id: '', name: '' },
      editingStudent: null,
    };
  },
  methods: {
    addStudent() {
      if (this.newStudent.id && this.newStudent.name) {
        this.students.push({ ...this.newStudent });
        this.newStudent = { id: '', name: '' };
      }
    },
    editStudent(student) {
      this.editingStudent = { ...student };
    },
    updateStudent() {
      const index = this.students.findIndex(s => s.id === this.editingStudent.id);
      if (index !== -1) this.students[index] = { ...this.editingStudent };
      this.editingStudent = null;
    },
    removeStudent(id) {
      this.students = this.students.filter(s => s.id !== id);
    },
    cancelEdit() {
      this.editingStudent = null;
    },
  },
};
</script>

<style scoped>
h2, h3 {
  text-align: center;
  font-weight: bold;
}

.table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}

.table th, .table td {
  border: 1px solid #ccc;
  padding: 0.5rem;
  text-align: center;
}

.btn-edit, .btn-delete, .btn-cancel {
  margin: 2px;
  padding: 5px 10px;
  border-radius: 6px;
  font-size: 0.9rem;
  cursor: pointer;
}

.btn-edit {
  background-color: #ffc107;
  border: none;
  color: #000;
}

.btn-delete {
  background-color: #dc3545;
  border: none;
  color: white;
}

.btn-cancel {
  background-color: #6c757d;
  border: none;
  color: white;
}

.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: hsla(0, 0%, 95%, 0.813);
}

.login-box {
  background-color: white;
  padding: 2rem;
  border-radius: 20px;
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 600px;
}

.login-content {
  width: 100%;
  text-align: left;
}

.form-input {
  position: relative;
  display: flex;
  align-items: center;
}

.form-input i {
  position: absolute;
  left: 10px;
  font-size: 20px;
}

input {
  width: 100%;
  padding: 8px 10px 8px 35px;
  border: 1px solid #ccc;
  border-radius: 10px;
  margin-bottom: 0.6rem;
}
</style>
