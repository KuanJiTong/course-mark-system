<template>
  <div class="component-marks-page">
    <div class="page-header">
      <h2>Continuous Assessment Components</h2>
      <div v-if="components.length" style="margin-top: 10px;">
        <strong>Total Component Marks:</strong>
{{ totalComponentMark }} / 
<span v-if="maxComponentMark">{{ maxComponentMark }}</span>
<span v-else class="text-danger">Not Available</span>

      </div>
    </div>

    <select v-model="selectedCourseId" class="input-field" v-if="courses.length">
      <option disabled value="">-- Select Course --</option>
      <option v-for="course in courses" :key="course.course_id" :value="course.course_id">
        {{ course.course_name }}
      </option>
    </select>

    <select v-model="selectedSectionId" class="input-field" v-if="sections.length">
      <option disabled value="">-- Select Section --</option>
      <option v-for="section in sections" :key="section.section_id" :value="section.section_id">
        Section {{ section.section_number }}
      </option>
    </select>

    <!-- Component Creation Section -->
    <div class="component-form">
      <input v-model="newComponent.component_name" placeholder="Component Name" class="input-field" />
      <input type="number" v-model="newComponent.max_mark" placeholder="Max Mark" class="input-field" />
      <button @click="addComponent" class="btn add-btn">➕ Add Component</button>
    </div>

    <div v-if="editMode" class="component-form edit-form">
    <input v-model="editComponentData.component_name" class="input-field" />
    <input v-model="editComponentData.max_mark" type="number" class="input-field" />
    <button @click="updateComponent" class="btn add-btn">✅ Update</button>
    <button @click="cancelEdit" class="btn">❌ Cancel</button>
  </div>

    <div class="marks-table-container" v-if="components.length">
      <table class="marks-table">
        <thead>
          <tr>
            <th>Component</th>
            <th>Max Mark</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="component in components" :key="component.component_id">
            <td>{{ component.component_name }}</td>
            <td>{{ component.max_mark }}</td>
            <td>
              <router-link
                :to="{ name: 'ComponentMarkPage', params: { componentId: component.component_id } }"
                class="btn btn-primary"
              >
                ➡ Enter Marks
              </router-link>

              <button @click="editComponent(component)" class="btn btn-warning">✏ Edit</button>
              <button @click="deleteComponent(component.component_id)" class="btn btn-danger">🗑 Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>


<script>
export default {
  data() {
    return {
      userID: null,
      selectedCourseId: '',
      selectedSectionId: '',
      courses: [],
      sections: [],
      students: [], 
      newComponent: {
        component_name: '',
        max_mark: ''
      },
      selectedCourse: null,
      components: [],
      marks: [],
      editMode: false,
      editComponentData: {
        component_id: null,
        component_name: '',
        max_mark: ''
      }
    };
  },

  computed: {
  
  maxComponentMark() {
  const cm = this.selectedCourse?.max_cm;
  return cm !== null && cm !== undefined ? cm : null;
},
  totalComponentMark() {
    return this.components.reduce((sum, c) => {
      const mark = parseFloat(c.max_mark);
      return sum + (isNaN(mark) ? 0 : mark);
    }, 0);
  }
},

  methods: {
    async fetchSections() {
      if (!this.selectedCourseId) return;

      const res = await fetch(`http://localhost:3000/sections?course_id=${this.selectedCourseId}`);
      const data = await res.json();
      this.sections = data;
    },
    async fetchCourses() {
      console.log("Courses:", this.courses);

  try {
    const res = await fetch(`http://localhost:3000/courses`);
    const data = await res.json();
    this.courses = data;

    // Set selectedCourse if already selected
    if (this.selectedCourseId) {
      this.selectedCourse = this.courses.find(c => c.course_id === this.selectedCourseId);
    }

  } catch (err) {
    console.error("Failed to fetch courses:", err);
    alert("Failed to load courses. Check your backend.");
  }
},

    async fetchComponents() {
      if (!this.selectedCourseId || !this.selectedSectionId) return;

      try {
        const [componentsRes, studentsRes, marksRes] = await Promise.all([
          fetch(`http://localhost:3000/components?course_id=${this.selectedCourseId}&section_id=${this.selectedSectionId}`),
          fetch(`http://localhost:3000/students?course_id=${this.selectedCourseId}&section_id=${this.selectedSectionId}`),
          fetch(`http://localhost:3000/marks?course_id=${this.selectedCourseId}&section_id=${this.selectedSectionId}`)
        ]);

        const components = await componentsRes.json();
        const students = await studentsRes.json();
        const marks = await marksRes.json();

        if (!Array.isArray(students)) {
          alert("Error loading students. Check if section is selected.");
          return;
        }

        this.students = students;

        this.components = components.map(component => {
          const marksPerStudent = students.map(student => {
            const match = marks.find(
              m => m.student_id === student.student_id && m.component_id === component.component_id
            );

            return {
              student_id: student.student_id,
              student_name: student.student_name,
              mark: match ? match.mark : ''
            };
          });

          return {
            ...component,
            marks: marksPerStudent
          };
        });
      } catch (error) {
        console.error("Fetch components error:", error);
        alert("Failed to fetch data. Make sure server is running.");
      }
    },


    async addComponent() {
      if (!this.selectedCourseId || !this.selectedSectionId) return;

      const payload = {
        course_id: this.selectedCourseId,
        section_id: this.selectedSectionId,  
        component_name: this.newComponent.component_name,
        max_mark: this.newComponent.max_mark
      };

      try {
        const res = await fetch('http://localhost:3000/components', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });

        const data = await res.json();
        alert(data.message || 'Component saved');

        // Clear form
        this.newComponent.component_name = '';
        this.newComponent.max_mark = '';

        // Refresh component list
        await this.fetchComponents();

      } catch (error) {
        alert("Failed to add component.");
        console.error(error);
      }
    },


    async saveMark(componentId, studentId, mark) {
      const payload = {
        component_id: componentId,
        student_id: studentId,
        mark: mark
      };

      const res = await fetch('http://localhost:3000/marks', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      alert(data.message || 'Mark saved');
    },
    async fetchMarks() {
      if (!this.selectedCourseId) return;

      const res = await fetch(`http://localhost:3000/marks?course_id=${this.selectedCourseId}`);
      const data = await res.json();
      this.marks = data;

      // Map marks to components
      this.components.forEach(component => {
        const markRecord = this.marks.find(m => m.component_id === component.component_id);
        if (markRecord) {
          component.student_id = markRecord.student_id;
          component.mark = markRecord.mark;
        }
      });
    },editComponent(component) {
  this.editMode = true;
  this.editComponentData = { ...component };
},

cancelEdit() {
  this.editMode = false;
  this.editComponentData = { component_id: null, component_name: '', max_mark: '' };
},

async updateComponent() {
  try {
    const res = await fetch(`http://localhost:3000/components/${this.editComponentData.component_id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(this.editComponentData)
    });
    const data = await res.json();
    alert(data.message || 'Component updated');
    this.cancelEdit();
    this.fetchComponents();
  } catch (err) {
    console.error(err);
    alert("Failed to update component");
  }
},

async deleteComponent(componentId) {
  if (!confirm('Are you sure you want to delete this component?')) return;

  try {
    const res = await fetch(`http://localhost:3000/components/${componentId}`, {
      method: 'DELETE'
    });
    const data = await res.json();
    alert(data.message || 'Component deleted');
    this.fetchComponents();
  } catch (err) {
    console.error(err);
    alert("Failed to delete component");
  }
}


  },
   watch: {
  selectedCourseId(newVal) {
    if (newVal) {
      localStorage.setItem('selectedCourseId', newVal);
      this.selectedCourse = this.courses.find(c => c.course_id === newVal) || null;
      this.fetchComponents();
      this.fetchSections();
      this.components = [];
    } else {
      localStorage.removeItem('selectedCourseId');
      this.selectedCourse = null;
      this.sections = [];
      this.components = [];
    }
  },
  selectedSectionId(newVal) {
    if (newVal) {
      localStorage.setItem('selectedSectionId', newVal); // ⬅ Save
      this.fetchComponents();
    } else {
      localStorage.removeItem('selectedSectionId');
    }
  }
},
  mounted() {
    // Check authentication
    const user = JSON.parse(sessionStorage.getItem('user'));
    if (!user || !user.user_id) {
      this.$router.push('/login?message=Please login to access component marks');
      return;
    }
    
    this.userID = user.user_id;
    console.log('Authenticated user ID for component marks:', this.userID);
    
    this.fetchCourses().then(() => {
      const savedCourseId = localStorage.getItem('selectedCourseId');
      const savedSectionId = localStorage.getItem('selectedSectionId');
      if (savedCourseId) this.selectedCourseId = savedCourseId;
      if (savedSectionId) this.selectedSectionId = savedSectionId;
    });
  }
};
</script>


<style scoped>
.component-marks-page {
  padding: 20px;
}
.page-header {
  text-align: center;
  margin-bottom: 20px;
}
.component-form {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}
.input-field {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  flex: 1;
}
.btn {
  padding: 8px 16px;
  border: none;
  cursor: pointer;
  border-radius: 4px;
}
.add-btn {
  background-color: #4CAF50;
  color: white;
}
.marks-table {
  width: 100%;
  border-collapse: collapse;
}
.marks-table th,
.marks-table td {
  border: 1px solid #ccc;
  padding: 10px;
  text-align: center;
}
</style>  