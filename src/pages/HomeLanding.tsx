import React from 'react';
import { LucideHardHat, LucideUsers, LucideSettings, LucideCheckCircle, LucideArrowRight, LucideMail, LucideArrowUpRight, LucideStar } from 'lucide-react';
import { Link } from 'react-router-dom';

export default function HomeLanding() {
  return (
    <div className="min-h-screen bg-white font-sans text-slate-900 flex flex-col">
      {/* Hero Section with Nav */}
      <div className="relative min-h-[90vh] flex flex-col">
        {/* Background Image & Overlay */}
        <div 
          className="absolute inset-0 bg-cover bg-center bg-no-repeat"
          style={{ backgroundImage: "url('https://images.unsplash.com/photo-1504307651254-35680f356dfd?q=80&w=2670&auto=format&fit=crop')" }}
        ></div>
        <div className="absolute inset-0 bg-gradient-to-r from-black/90 md:from-black/80 via-black/50 to-transparent"></div>
        <div className="absolute inset-0 bg-black/20"></div>

        {/* Navbar */}
        <nav className="relative z-50 flex items-center justify-between px-6 md:px-12 py-6 max-w-[1400px] mx-auto w-full">
          <div className="flex items-center space-x-2 text-white">
            <div className="w-8 h-8 bg-white text-black rounded-full flex items-center justify-center">
              <LucideHardHat size={18} />
            </div>
            <span className="font-bold text-xl tracking-wide">SMART UJENZI</span>
          </div>
          
          <div className="hidden lg:flex items-center space-x-8 text-sm font-medium text-white/90">
            <a href="#" className="hover:text-white transition">Home</a>
            <a href="#" className="hover:text-white transition flex items-center">About Us <span className="ml-1 text-[10px]">▼</span></a>
            <a href="#" className="hover:text-white transition flex items-center">Services <span className="ml-1 text-[10px]">▼</span></a>
            <a href="#" className="hover:text-white transition flex items-center">Projects <span className="ml-1 text-[10px]">▼</span></a>
            <a href="#" className="hover:text-white transition">Blog</a>
            <a href="#" className="hover:text-white transition">Contact</a>
          </div>

          <div>
            <Link 
              to="/login" 
              className="bg-white text-black px-5 py-2.5 rounded-lg font-semibold text-sm hover:bg-gray-100 transition shadow-lg flex items-center space-x-2"
            >
              <span>Login to Portal</span>
              <LucideArrowRight size={16} />
            </Link>
          </div>
        </nav>

        {/* Hero Content */}
        <div className="relative z-10 flex-1 flex flex-col justify-center px-6 md:px-12 max-w-[1400px] mx-auto w-full pb-32 pt-10">
          <div className="max-w-2xl">
            {/* Badge */}
            <div className="inline-flex items-center space-x-2 bg-black/40 backdrop-blur-sm rounded-full p-1 pr-4 mb-6 border border-white/10">
              <span className="bg-white/20 text-white text-xs font-semibold px-3 py-1.5 rounded-full">We're Hiring!</span>
              <span className="text-white text-xs font-medium flex items-center hover:opacity-80 cursor-pointer">
                Join our Remote Team <LucideArrowRight size={14} className="ml-1" />
              </span>
            </div>

            <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-[1.1] tracking-tight">
              We Use Modern Tech to Deliver Stronger, Smarter Construction Management
            </h1>
            
            <p className="text-gray-300 text-lg mb-10 max-w-xl leading-relaxed">
              From tracking tasks to managing resources, we've been Tanzania's trusted construction management system—delivering on-time, budget-friendly projects with rock-solid reliability.
            </p>

            {/* Email Input */}
            <div className="flex bg-white/10 backdrop-blur-md border border-white/20 p-1.5 rounded-xl max-w-md mb-10">
              <div className="flex items-center pl-4 pr-3 text-gray-400">
                <LucideMail size={20} />
              </div>
              <input 
                type="email" 
                placeholder="Enter email address..." 
                className="bg-transparent border-none text-white outline-none flex-1 placeholder-gray-400 text-sm" 
              />
              <Link 
                to="/login" 
                className="bg-white text-black px-6 py-3 rounded-lg font-bold text-sm hover:bg-gray-100 transition shadow-lg whitespace-nowrap"
              >
                Get Started
              </Link>
            </div>

            {/* Reviews / Social Proof */}
            <div className="flex flex-col sm:flex-row sm:items-center space-y-6 sm:space-y-0 sm:space-x-12">
              <div className="flex items-center space-x-4">
                <div className="flex -space-x-3">
                  <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=compress&fit=crop&w=100&h=100" className="w-10 h-10 rounded-full border-2 border-slate-900" alt="avatar" referrerPolicy="no-referrer" />
                  <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=compress&fit=crop&w=100&h=100" className="w-10 h-10 rounded-full border-2 border-slate-900" alt="avatar" referrerPolicy="no-referrer" />
                  <img src="https://images.unsplash.com/photo-1599566150163-29194dcaad36?auto=compress&fit=crop&w=100&h=100" className="w-10 h-10 rounded-full border-2 border-slate-900" alt="avatar" referrerPolicy="no-referrer" />
                  <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=compress&fit=crop&w=100&h=100" className="w-10 h-10 rounded-full border-2 border-slate-900" alt="avatar" referrerPolicy="no-referrer" />
                </div>
                <div className="text-white text-xs leading-tight">
                  <span className="font-semibold block">15+ Construction Specialists</span>
                  <span className="text-gray-400">Dedicated to Your Project's Success</span>
                </div>
              </div>

              <div className="flex space-x-8">
                <div>
                  <div className="flex text-yellow-500 mb-1">
                    <LucideStar size={14} fill="currentColor" />
                    <LucideStar size={14} fill="currentColor" />
                    <LucideStar size={14} fill="currentColor" />
                    <LucideStar size={14} fill="currentColor" />
                    <LucideStar size={14} fill="currentColor" />
                  </div>
                  <div className="text-white text-xs font-medium">4.9/5 (25k+ Reviews)</div>
                </div>
                <div>
                  <div className="flex text-green-500 mb-1">
                    <LucideStar size={14} fill="currentColor" />
                    <LucideStar size={14} fill="currentColor" />
                    <LucideStar size={14} fill="currentColor" />
                    <LucideStar size={14} fill="currentColor" />
                    <LucideStar size={14} fill="currentColor" />
                  </div>
                  <div className="text-white text-xs font-medium">4.8/5 (64k+ Reviews)</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Partners Banner at bottom of hero */}
        <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent pt-12 pb-6 px-6 md:px-12">
          <div className="max-w-[1400px] mx-auto flex flex-col md:flex-row items-center justify-between border-t border-white/15 pt-6 gap-6">
            <p className="text-white/80 font-medium text-sm w-full md:w-auto">
              Trusted by hundreds of<br/>companies in Tanzania
            </p>
            <div className="flex space-x-6 md:space-x-12 opacity-70 grayscale overflow-x-auto w-full md:w-auto pb-2 md:pb-0 hide-scrollbar">
              {/* Mock logos text for design sake */}
              <div className="text-white font-bold text-xl tracking-tighter flex items-center space-x-2 shrink-0">
                <div className="w-6 h-6 bg-white rounded-md"></div><span>logoipsum</span>
              </div>
              <div className="text-white font-bold text-xl tracking-tighter flex items-center space-x-2 shrink-0">
                <div className="w-6 h-6 rounded-full border-2 border-white"></div><span>logoipsum</span>
              </div>
              <div className="text-white font-bold text-xl tracking-tighter flex items-center space-x-2 shrink-0">
                <div className="w-6 h-6 bg-white rotate-45"></div><span>logoipsum</span>
              </div>
              <div className="text-white font-bold text-xl tracking-tighter flex items-center space-x-2 shrink-0">
                <div className="w-6 h-6 bg-white rounded-full"></div><span>logoipsum</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* About Us Section */}
      <div className="bg-white py-24 px-6 md:px-12">
        <div className="max-w-[1400px] mx-auto flex flex-col lg:flex-row gap-16 xl:gap-24 items-center">
          
          <div className="w-full lg:w-1/2">
            <div className="flex items-center space-x-2 text-sm font-bold tracking-wide mb-6">
              <span>About Us</span>
              <LucideArrowRight size={16} />
            </div>
            
            <h2 className="text-3xl md:text-4xl lg:text-[42px] font-bold text-slate-900 mb-8 leading-[1.2] tracking-tight">
              Deep Roots in Tanzania: Builders Who Understand Your Needs
            </h2>
            
            <p className="text-slate-600 mb-6 leading-relaxed text-lg">
              Locally focused and technologically advanced, we've been transforming Tanzania's construction management processes. Our advanced system combines traditional workflow needs with modern innovation to streamline every aspect of your project.
            </p>
            
            <p className="text-slate-600 mb-10 leading-relaxed text-lg">
              From resource allocation to tracking multiple sites, every feature reflects our commitment to transparency, efficiency, and your complete satisfaction. We don't just manage projects — we help you build the future confidently.
            </p>
            
            <button className="bg-[#111] hover:bg-black text-white px-6 py-4 rounded-xl font-bold flex items-center space-x-2 mb-12 transition shadow-md">
              <span>Go to About Us Page</span>
              <LucideArrowUpRight size={18} />
            </button>
            
            <div className="grid grid-cols-2 md:grid-cols-3 gap-8 border-t border-gray-200 pt-10">
              <div>
                <div className="text-3xl lg:text-4xl font-bold text-slate-900 mb-2">200+</div>
                <div className="text-sm font-medium text-slate-500">Tanzania Projects</div>
              </div>
              <div>
                <div className="text-3xl lg:text-4xl font-bold text-slate-900 mb-2">15 Year</div>
                <div className="text-sm font-medium text-slate-500">System Reliability</div>
              </div>
              <div>
                <div className="text-3xl lg:text-4xl font-bold text-slate-900 mb-2">4.8/5</div>
                <div className="text-sm font-medium text-slate-500">Client Rating</div>
              </div>
            </div>
          </div>
          
          {/* Images Grid */}
          <div className="w-full lg:w-1/2 relative h-[500px] sm:h-[600px] lg:h-[700px]">
            {/* Tall left image */}
            <div className="absolute top-0 left-0 w-[48%] h-full rounded-2xl overflow-hidden shadow-lg">
              <img 
                src="https://images.unsplash.com/photo-1541888081622-1de23f5b08e2?q=80&w=800&auto=format&fit=crop" 
                alt="Construction Worker" 
                referrerPolicy="no-referrer"
                className="w-full h-full object-cover object-center"
              />
            </div>
            
            {/* Top right image */}
            <div className="absolute top-0 right-0 w-[48%] h-[48%] rounded-2xl overflow-hidden shadow-lg">
              <img 
                src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=800&auto=format&fit=crop" 
                alt="Team Meeting" 
                referrerPolicy="no-referrer"
                className="w-full h-full object-cover object-center"
              />
            </div>
            
            {/* Bottom right image */}
            <div className="absolute bottom-0 right-0 w-[48%] h-[48%] rounded-2xl overflow-hidden shadow-lg">
              <img 
                src="https://images.unsplash.com/photo-1503387762-592deb58ef4e?q=80&w=800&auto=format&fit=crop" 
                alt="Construction Site interior" 
                referrerPolicy="no-referrer"
                className="w-full h-full object-cover object-center"
              />
            </div>
          </div>

        </div>
      </div>

      {/* Footer */}
      <footer className="bg-[#0C0D10] text-[#7A7A7E] py-12 border-t border-white/5">
        <div className="max-w-[1400px] mx-auto px-6 md:px-12 flex flex-col md:flex-row justify-between items-center gap-6">
          <div className="text-center md:text-left">
            <div className="flex items-center justify-center md:justify-start text-white text-lg font-bold tracking-wider mb-2">
              <span className="w-6 h-6 bg-yellow-500 text-slate-900 rounded-full flex items-center justify-center mr-2">
                <LucideHardHat size={14} />
              </span>
              SMART UJENZI
            </div>
            <p className="text-sm">Final Year Project &copy; {new Date().getFullYear()}</p>
          </div>
          <div className="text-center md:text-right">
            <p className="font-semibold text-white mb-1">Teleza Abdallah Mkomwa</p>
            <p className="text-sm">Bachelor of Computer Science III</p>
            <p className="text-sm">Institute of Accountancy Arusha</p>
          </div>
        </div>
      </footer>
    </div>
  );
}

