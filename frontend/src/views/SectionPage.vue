<template>
    <button class="btn btn-secondary mb-4" @click="goBack">Back</button>
    <h2 class="mb-4" v-if="course">Section Page - {{ course.courseCode }} {{ course.courseName }}</h2>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2 flex-wrap">
      <div>
        <button
          class="mb-2"
          :class="showForm ? 'btn btn-danger' : 'btn btn-success'"
          @click="showForm ? reset() : toggleForm()"
        >
          {{ showForm ? 'Cancel' : 'Add Section' }}
        </button>

        <div v-if="showForm" class="d-flex align-items-center gap-2 mb-3">
          <input type="text" v-model="newSection.sectionNumber" placeholder="Section No." class="form-control" style="width: 150px;">
          <input type="text" v-model="newSection.capacity" placeholder="Capacity" class="form-control" style="width: 250px;">
          <button class="btn btn-primary" @click="addSection">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <div class="table-responsive shadow-sm rounded">
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th style="width: 50px;">#</th>
          <th>Section Number</th>
          <th>Capacity</th>
          <th># of students</th>
          <th>Lecturer</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="sections.length === 0" class="text-center text-muted">
          <td colspan="8">No section added yet.</td>
        </tr>
        <tr v-for="(section, index) in sections" :key="index">
          <td>{{ index + 1 }}</td>
          <td>
            <span v-if="editingSectionId !== section.sectionId">{{ section.sectionNumber }}</span>
            <input v-else style="width: 150px;" v-model="section.sectionNumber" type="text" />
          </td>
          <td>
            <span v-if="editingSectionId !== section.sectionId">{{ section.capacity }}</span>
            <input v-else style="width: 100%;" v-model="section.capacity" type="text" />
          </td>
          <td></td>
          <td></td>

          <td class="text-center">
            <div class="icon-row">
              <!-- When NOT editing -->
              <template v-if="editingSectionId !== section.sectionId">
                <i class="bi bi-person-plus-fill mx-2" data-bs-toggle="tooltip" title="Assign Lecturer"></i>
                <i class="bi bi-pencil-square text-primary mx-2" data-bs-toggle="tooltip" title="Edit" @click="startEdit(section)"></i>
                <i class="bi bi-trash-fill text-danger mx-2" data-bs-toggle="tooltip" title="Delete"></i>
              </template>

              <!-- When editing -->
              <template v-else>
                <button class="btn btn-sm btn-primary mx-1" @click="updateSection(section)">Submit</button>
                <button class="btn btn-sm btn-danger mx-1" @click="cancelEdit(section)">Cancel</button>
              </template>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: "SectionPage",
  data() {
    return {
      courseId: null,
      course: null,
      editingSectionId: null,
      newSection: {
        sectionNumber: '',
        capacity: null,
        courseId: null
      },
      sections: [],
      showForm: false,
    };
  },
  async created(){
      this.courseId = this.$route.params.courseId;
      await this.fetchCourse();
      await this.fetchAllSections();
  },
  methods: {
    async fetchAllSections(){
      try {
        const courseId = this.courseId; 
        const url = `http://localhost:3000/section?course_id=${courseId}`;

        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch sections');

        const data = await response.json();
        this.sections = data; 
      } catch (error) {
        console.error('Error fetching sections:', error);
      }
    },
    async fetchCourse(){
      try {
        const courseId = this.courseId; 
        const url = `http://localhost:3000/course/${courseId}`;

        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch sections');

        const data = await response.json();
        this.course = data; 
      } catch (error) {
        console.error('Error fetching sections:', error);
      }
    },
    goBack() {
      this.$router.back();
    },
    reset(){
      this.newSection.sectionNumber = '';
      this.newSection.capacity = null;
      this.showForm = false;
    },
    toggleForm(){
      this.showForm = true;
    },
    startEdit(section) {
      if (this.editingSectionId) {
        const prevSection = this.sections.find(c => c.sectionId === this.editingSectionId);
        if (prevSection && prevSection._backup) {
          Object.assign(prevSection, prevSection._backup);
          delete prevSection._backup;
        }
      }
      this.editingSectionId = section.sectionId;
      section._backup = { ...section };
    },
    cancelEdit(section) {
      if (section._backup) {
        Object.assign(section, section._backup);
        delete section._backup;
      }
      this.editingSectionId = null;
    },
    async addSection() {
      // Prepare the data to be sent
      this.newSection.courseId = this.courseId;
      const newSection = this.newSection;

      // Send POST request
      await fetch('http://localhost:3000/section', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(newSection)
      })
      .then(async response => {
        if (!response.ok) {
          throw new Error('Failed to add section');
        }
        return await response.json();
      })
      .then(data => {
        alert('Section added:', data);

        this.reset();

        // Fetch the updated section list
        this.fetchAllSections();
      })
      .catch(error => {
        console.error('Error:', error);
      });
    },    
    async updateSection(section){
      this.editingSectionId = null;
      delete section._backup;
      try {
        const response = await fetch(`http://localhost:3000/section/${section.sectionId}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(section),
        });

        if (!response.ok) {
          const error = await response.json();
          console.error('Update failed:', error);
          alert('Failed to update section.');
        } else {
          alert('Section updated successfully.');
          this.fetchAllSections();
        }
      } catch (err) {
        console.error('Request error:', err);
        alert('Network error.');
      }
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
