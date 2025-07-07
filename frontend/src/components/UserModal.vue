<template>
  <div v-if="show" class="modal d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content rounded-3">
        <div class="modal-header text-center">
          <h5 class="modal-title w-100">{{ isEdit ? 'Edit User' : 'Add User' }}</h5>
          <button type="button" class="btn-close" aria-label="Close" @click="$emit('close')"></button>
        </div>
        <form @submit.prevent="handleSubmit" >
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label">Login ID</label>
              <input v-model="user.loginId" type="text" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Role</label>
              <div class="d-flex gap-3 flex-wrap">
                <label v-for="role in roles" :key="role.roleId" class="form-check form-check-inline">
                  <input
                    type="checkbox"
                    class="form-check-input"
                    :value="Number(role.roleId)"
                    v-model.number="user.roleIds"
                  >
                  {{ role.roleName }}
                </label>
              </div>
            </div>
            <div v-if="user.roleIds.includes(2)" class="mb-3">
              <label class="form-label">Title</label>
              <select
                      v-model="user.title"
                      class="form-select"
                      required>
                <option disabled value=''>Select Title</option>
                <option value="Dr.">Dr.</option>
                <option value="Prof.">Prof.</option>
                <option value="Prof. Dr.">Prof. Dr.</option>
                <option value="Assoc. Prof.">Assoc. Prof.</option>
                <option value="Assoc. Prof. Dr.">Assoc. Prof. Dr.</option>
                <option value="Prof. Madya Dr.">Prof. Madya Dr.</option>
                <option value="Ir.">Ir.</option>
                <option value="Ir. Dr.">Ir. Dr.</option>
                <option value="Ts.">Ts.</option>
                <option value="Ts. Dr.">Ts. Dr.</option>
                <option value="Mr.">Mr.</option>
                <option value="Ms.">Ms.</option>
                <option value="Mdm.">Mdm.</option>
                <option value="Dato'">Dato'</option>
                <option value="Datin">Datin</option>
                <option value="Hj.">Hj.</option>
                <option value="Hjh.">Hjh.</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input v-model="user.name" type="text" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input v-model="user.email" type="email" class="form-control" required />
            </div>
            <div v-if="!isEdit" class="mb-3">
              <label class="form-label">Password</label>
              <input v-model="user.password" type="password" class="form-control" required/>
            </div>
            <div class="mb-3">
              <label class="form-label">Faculty</label>
              <select v-model.number="user.facultyId" class="form-select" required>
                <option disabled :value="null">Select Faculty</option>
                <option v-for="faculty in faculties" :key="faculty.facultyId" :value="Number(faculty.facultyId)">
                  {{ faculty.facultyName }} ({{ faculty.facultyAbbreviation }})
                </option>
              </select>
            </div>
            <!-- Program for Student (role ID 4) -->
            <div v-if="user.roleIds.includes(4)" class="mb-3">
              <label class="form-label">Program</label>
              <select v-model="user.program" class="form-select" required>
                <option disabled value=''>Select Program</option>
                <option v-for="program in programs" :key="program" :value="program">
                  {{ program }}
                </option>
              </select>
            </div>

            <!-- Program for Student (role ID 4) -->
            <div v-if="user.roleIds.includes(2)" class="mb-3">
              <label class="form-label">Department</label>
              <select v-model="user.department" class="form-select" required>
                <option disabled value=''>Select Program</option>
                <option v-for="department in departments" :key="department" :value="department">
                  {{ department }}
                </option>
              </select>
            </div>
            
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" @click="$emit('close')">Cancel</button>
            <button type="submit" class="btn btn-primary">
              {{ isEdit ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { getCurrentDateTime } from '../Utils/dateFormatter'; 
export default {
  props: {
    show: Boolean,
    userData: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      user: {
        loginId: '',
        title: '',
        name: '',
        email: '',
        password: '',
        createdAt: '',
        facultyId: null,
        program: '',
        department: '',
        roleIds: []
      },//{ "userId": "1", "loginId": "A22EC0062", "name": "KUAN JI TONG", "email": "kuantong@graduate.utm.my", "facultyAbbreviation": "FC", "roleNames": [ "Student" ] }
      roles: null,
      faculties: null,
      programs: [
        'Bachelor of Computer Science (Software Engineering)',
        'Bachelor of Computer Science (Data Engineering)',
        'Bachelor of Computer Science (Graphics & Multimedia Software)',
        'Bachelor of Computer Science (Network & Security)',
        'Bachelor of Computer Science (Bioinformatics)',
        'Bachelor of Computer Science (Artificial Intelligence)'
      ],
      departments: [
        'Applied Computing',
        'Computer Science',
        'Emergent Computing',
        'Software Engineering'
      ]
    };
  },
  computed: {
    isEdit() {
      return !!this.userData.userId;
    }
  },
  watch: {
    userData: {
      immediate: true,
      handler(newVal) {
        if (this.isEdit && newVal && Object.keys(newVal).length) {
          this.user = {
  // Start with defaults
  loginId: '',
  title: '',
  name: '',
  email: '',
  password: '',
  createdAt: '',
  facultyId: null,
  program: '',
  department: '',
  roleIds: [],

  // Overwrite with incoming values
  ...newVal
};

// Normalize types
this.user.roleIds = (newVal.roleIds || []).map(id => Number(id));
this.user.password = ''; // Always blank out password when editing

          console.log('Loaded user for edit:', this.user);
        } else {
          this.reset();
        }
      }
    },
  'user.roleIds'(newVal) {
    const roleIds = newVal.map(Number); // ensure they're all numbers

    const valid =
      roleIds.length === 0 ||
      roleIds.length === 1 ||
      (roleIds.length === 2 && roleIds.includes(2) && roleIds.includes(3));

    if (!valid) {
      alert('Only "Lecturer" and "Academic Advisor" can be selected together.');

      // Also ensure we reset to numbers
      if (this.isEdit && this.userData.roleIds) {
        this.user.roleIds = this.userData.roleIds.map(Number);
      } else {
        this.user.roleIds = [];
      }
    }
  }
  },
  async created(){
    await this.fetchAllfaculties();
    await this.fetchAllRoles();
  },
  methods: {
    async fetchAllfaculties(){
      try {
        const url = `http://localhost:3000/faculty`;

        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch faculties');

        const data = await response.json();
        this.faculties = data; 
      } catch (error) {
        console.error('Error fetching faculties:', error);
      }
    },
    async fetchAllRoles(){
      try {
        const url = `http://localhost:3000/role`;

        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch roles');

        const data = await response.json();
        this.roles = data; 
      } catch (error) {
        console.error('Error fetching roles:', error);
      }
    },
    reset(){
      this.user.loginId = '';
      this.user.title = '';
      this.user.name = '';
      this.user.email = '';
      this.user.password = '';
      this.user.createdAt = '';
      this.user.facultyId = null;
      this.user.roleIds = [];
      this.user.program = '';
      this.user.department = '';
    },
    handleSubmit() {
      if (!this.isEdit) {
        this.user.createdAt = getCurrentDateTime();
      }
      this.$emit('submit-user', { ...this.user });
      this.reset();
    }

  }
};
</script>
