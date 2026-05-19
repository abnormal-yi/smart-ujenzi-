import React from 'react';
import { useState, useEffect } from 'react';
import { LucidePlus, LucideSearch } from 'lucide-react';

export default function Projects() {
  const [projects, setProjects] = useState<any[]>([]);
  const [users, setUsers] = useState<any[]>([]);
  const [showModal, setShowModal] = useState(false);
  const [formData, setFormData] = useState({ name: '', description: '', manager_id: '', start_date: '', end_date: '' });

  const fetchData = async () => {
    const token = localStorage.getItem('token');
    const headers = { 'Authorization': `Bearer ${token}` };
    
    const [projRes, userRes] = await Promise.all([
      fetch('/api/projects', { headers }),
      fetch('/api/users', { headers })
    ]);
    
    if (projRes.ok) setProjects(await projRes.json());
    if (userRes.ok) setUsers(await userRes.json());
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    const token = localStorage.getItem('token');
    const res = await fetch('/api/projects', {
      method: 'POST',
      headers: { 
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json' 
      },
      body: JSON.stringify(formData)
    });
    
    if (res.ok) {
      setShowModal(false);
      fetchData();
      setFormData({ name: '', description: '', manager_id: '', start_date: '', end_date: '' });
    }
  };

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold text-gray-800">Projects</h2>
        <button 
          onClick={() => setShowModal(true)}
          className="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg flex items-center transition-colors"
        >
          <LucidePlus className="w-4 h-4 mr-2" />
          New Project
        </button>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div className="p-4 border-b border-gray-200 flex justify-between items-center">
          <div className="relative w-64">
            <LucideSearch className="w-4 h-4 absolute left-3 top-3 text-gray-400" />
            <input 
              type="text" 
              placeholder="Search projects..." 
              className="pl-9 pr-4 py-2 w-full border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500"
            />
          </div>
        </div>
        <table className="w-full text-left border-collapse">
          <thead>
            <tr className="bg-gray-50 text-gray-500 text-sm border-b border-gray-200">
              <th className="p-4 font-medium">Project Name</th>
              <th className="p-4 font-medium">Manager</th>
              <th className="p-4 font-medium">Start Date</th>
              <th className="p-4 font-medium">End Date</th>
              <th className="p-4 font-medium">Status</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-200">
            {projects.map((proj) => (
              <tr key={proj.id} className="hover:bg-gray-50">
                <td className="p-4 font-medium text-gray-900">{proj.name}</td>
                <td className="p-4 text-gray-600">{proj.manager_name || 'Unassigned'}</td>
                <td className="p-4 text-gray-600">{proj.start_date || 'N/A'}</td>
                <td className="p-4 text-gray-600">{proj.end_date || 'N/A'}</td>
                <td className="p-4">
                  <span className={`px-2.5 py-1 rounded-full text-xs font-medium ${
                    proj.status === 'Ongoing' ? 'bg-blue-100 text-blue-800' :
                    proj.status === 'Completed' ? 'bg-green-100 text-green-800' :
                    'bg-yellow-100 text-yellow-800'
                  }`}>
                    {proj.status}
                  </span>
                </td>
              </tr>
            ))}
            {projects.length === 0 && (
              <tr>
                <td colSpan={5} className="p-8 text-center text-gray-500">No projects found.</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
            <div className="p-4 border-b border-gray-200">
              <h3 className="text-lg font-bold text-gray-900">Create New Project</h3>
            </div>
            <form onSubmit={handleSubmit} className="p-4 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Project Name</label>
                <input required type="text" value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea value={formData.description} onChange={e => setFormData({...formData, description: e.target.value})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500" rows={3}></textarea>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Manager</label>
                <select value={formData.manager_id} onChange={e => setFormData({...formData, manager_id: e.target.value})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500">
                  <option value="">Select Manager</option>
                  {users.map(u => (
                    <option key={u.id} value={u.id}>{u.name}</option>
                  ))}
                </select>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                  <input type="date" value={formData.start_date} onChange={e => setFormData({...formData, start_date: e.target.value})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                  <input type="date" value={formData.end_date} onChange={e => setFormData({...formData, end_date: e.target.value})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500" />
                </div>
              </div>
              <div className="pt-4 flex justify-end space-x-3 border-t">
                <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" className="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900">Create Project</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
