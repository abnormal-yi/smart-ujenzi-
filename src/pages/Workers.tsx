import React, { useState, useEffect } from 'react';
import { LucidePlus, LucideWrench, LucideUsers, LucideHardHat, LucideBriefcase } from 'lucide-react';

export default function Workers() {
  const [resources, setResources] = useState<any[]>([]);
  const [allocations, setAllocations] = useState<any[]>([]);
  const [projects, setProjects] = useState<any[]>([]);
  const [showModal, setShowModal] = useState(false);
  const [assignModal, setAssignModal] = useState<{id: number, name: string} | null>(null);

  const [formData, setFormData] = useState({ type: 'labor', name: '', details: '' });
  const [assignData, setAssignData] = useState({ project_id: '' });

  const fetchData = async () => {
    const token = localStorage.getItem('token');
    const headers = { 'Authorization': `Bearer ${token}` };
    
    const [resRes, allocRes, projRes] = await Promise.all([
      fetch('/api/resources', { headers }),
      fetch('/api/allocations', { headers }),
      fetch('/api/projects', { headers })
    ]);
    
    if (resRes.ok) setResources(await resRes.json());
    if (allocRes.ok) setAllocations(await allocRes.json());
    if (projRes.ok) setProjects(await projRes.json());
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleAddResource = async (e: React.FormEvent) => {
    e.preventDefault();
    const token = localStorage.getItem('token');
    const res = await fetch('/api/resources', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    });
    if (res.ok) {
      setShowModal(false);
      setFormData({ type: 'labor', name: '', details: '' });
      fetchData();
    }
  };

  const handleAssign = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!assignModal) return;
    const token = localStorage.getItem('token');
    const res = await fetch('/api/allocations', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json' },
      body: JSON.stringify({ project_id: assignData.project_id, item_id: assignModal.id })
    });
    if (res.ok) {
      setAssignModal(null);
      fetchData();
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold text-gray-800">Mafundi & Equipment</h2>
        <button 
          onClick={() => setShowModal(true)}
          className="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg flex items-center transition-colors"
        >
          <LucidePlus className="w-4 h-4 mr-2" />
          Add Resource
        </button>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        {/* Workers & Equipment List */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div className="p-4 border-b border-gray-200 bg-gray-50 flex items-center">
            <LucideUsers className="w-5 h-5 mr-2 text-slate-700" />
            <h3 className="font-bold text-slate-800">Available Resources</h3>
          </div>
          <ul className="divide-y divide-gray-100 max-h-96 overflow-y-auto">
            {resources.map((r) => (
              <li key={r.id} className="p-4 hover:bg-gray-50 flex justify-between items-center">
                <div className="flex items-center space-x-4">
                  <div className={`p-2 rounded-full ${r.type === 'labor' ? 'bg-orange-100 text-orange-600' : 'bg-indigo-100 text-indigo-600'}`}>
                    {r.type === 'labor' ? <LucideHardHat size={20} /> : <LucideWrench size={20} />}
                  </div>
                  <div>
                    <h4 className="font-medium text-gray-900">{r.name}</h4>
                    <p className="text-sm text-gray-500">{r.details}</p>
                  </div>
                </div>
                <button
                  onClick={() => setAssignModal(r)}
                  className="text-sm bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-3 py-1.5 rounded-md font-medium transition-colors"
                >
                  Assign Job
                </button>
              </li>
            ))}
            {resources.length === 0 && <li className="p-4 text-center text-gray-500">No resources defined.</li>}
          </ul>
        </div>

        {/* Current Allocations */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div className="p-4 border-b border-gray-200 bg-gray-50 flex items-center">
            <LucideBriefcase className="w-5 h-5 mr-2 text-slate-700" />
            <h3 className="font-bold text-slate-800">Current Allocations</h3>
          </div>
          <ul className="divide-y divide-gray-100 max-h-96 overflow-y-auto">
            {allocations.map((a) => (
              <li key={a.id} className="p-4 hover:bg-gray-50">
                <div className="flex justify-between items-start">
                  <div>
                    <h4 className="font-medium text-gray-900">{a.resource_name}</h4>
                    <p className="text-sm text-gray-500 mt-0.5">Assigned to: <span className="font-medium text-slate-700">{a.project_name}</span></p>
                  </div>
                  <span className="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full uppercase tracking-wider font-medium">
                    {a.resource_type}
                  </span>
                </div>
              </li>
            ))}
            {allocations.length === 0 && <li className="p-4 text-center text-gray-500">No active allocations.</li>}
          </ul>
        </div>
      </div>

      {/* Add Resource Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
            <div className="p-4 border-b border-gray-200">
              <h3 className="text-lg font-bold text-gray-900">Add Resource</h3>
            </div>
            <form onSubmit={handleAddResource} className="p-4 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select value={formData.type} onChange={e => setFormData({...formData, type: e.target.value})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500">
                  <option value="labor">Labor (Fundi)</option>
                  <option value="equipment">Equipment (Mashine/Vifaa)</option>
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input required type="text" value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500" placeholder="e.g. Fundi Juma or JCB Excavator" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Details/Skills</label>
                <textarea required value={formData.details} onChange={e => setFormData({...formData, details: e.target.value})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500" placeholder="e.g. Specialized in plumbing..." rows={3} />
              </div>
              <div className="pt-4 flex justify-end space-x-3 border-t">
                <button type="button" onClick={() => setShowModal(false)} className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" className="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900">Add Resource</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Assign Modal */}
      {assignModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-xl shadow-xl w-full max-w-sm overflow-hidden">
            <div className="p-4 border-b border-gray-200">
              <h3 className="text-lg font-bold text-gray-900">Assign Job</h3>
              <p className="text-sm text-gray-500">Allocating {assignModal.name}</p>
            </div>
            <form onSubmit={handleAssign} className="p-4 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Select Project</label>
                <select required value={assignData.project_id} onChange={e => setAssignData({project_id: e.target.value})} className="w-full p-2 border rounded-lg outline-none focus:ring-2 focus:ring-yellow-500">
                  <option value="">-- Choose Project --</option>
                  {projects.map(p => (
                    <option key={p.id} value={p.id}>{p.name}</option>
                  ))}
                </select>
              </div>
              <div className="pt-4 flex justify-end space-x-3 border-t">
                <button type="button" onClick={() => setAssignModal(null)} className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Cancel</button>
                <button type="submit" className="px-4 py-2 bg-green-600 text-white hover:bg-green-700 rounded-lg">Confirm Assignment</button>
              </div>
            </form>
          </div>
        </div>
      )}

    </div>
  );
}
