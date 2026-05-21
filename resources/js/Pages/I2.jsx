// import React from "react";
// import { useForm } from "@inertiajs/react";
// import "./I2.css";

// export default function ImportPage() {

//   const { data, setData, post, processing } = useForm({
//     file: null,
//     type: "global",
//   });

//   const handleSubmit = (e) => {
//     e.preventDefault();

//     post("/import", {
//       forceFormData: true,
//     });
//   };

//   return (
//     <div className="import-page">

//       <div className="container py-5">

//         <div className="import-card">

//           {/* Header */}
//           <div className="mb-5">
//             <h1 className="title">
//               Importation Excel
//             </h1>

//             <p className="subtitle">
//               Importez vos données facilement
//             </p>
//           </div>

//           {/* Types */}
//           <div className="row g-4 mb-5">

//             <div className="col-md-4">
//               <button
//                 type="button"
//                 onClick={() => setData("type", "global")}
//                 className={`type-card ${
//                   data.type === "global" ? "active" : ""
//                 }`}
//               >
//                 <h5>Import Global</h5>
//                 <p>Toutes les feuilles Excel</p>
//               </button>
//             </div>

//             <div className="col-md-4">
//               <button
//                 type="button"
//                 onClick={() => setData("type", "stagiaires")}
//                 className={`type-card ${
//                   data.type === "stagiaires" ? "active" : ""
//                 }`}
//               >
//                 <h5>Stagiaires</h5>
//                 <p>Importer les stagiaires</p>
//               </button>
//             </div>

//             <div className="col-md-4">
//               <button
//                 type="button"
//                 onClick={() => setData("type", "groupes")}
//                 className={`type-card ${
//                   data.type === "groupes" ? "active" : ""
//                 }`}
//               >
//                 <h5>Groupes</h5>
//                 <p>Importer les groupes</p>
//               </button>
//             </div>

//           </div>

//           {/* Upload */}
//           <form onSubmit={handleSubmit}>

//             <div className="upload-box">

//               <div className="upload-icon">
//                 📂
//               </div>

//               <h3>
//                 Choisir un fichier Excel
//               </h3>

//               <p>
//                 Formats supportés : .xlsx
//               </p>

//               <input
//                 type="file"
//                 className="form-control mt-4"
//                 accept=".xlsx,.xls"
//                 onChange={(e) =>
//                   setData("file", e.target.files[0])
//                 }
//               />

//               {data.file && (
//                 <div className="selected-file">
//                   {data.file.name}
//                 </div>
//               )}
//             </div>

//             {/* Footer */}
//             <div className="d-flex justify-content-between align-items-center mt-4">

//               <div>
//                 Type :
//                 <strong className="ms-2 text-capitalize">
//                   {data.type}
//                 </strong>
//               </div>

//               <button
//                 type="submit"
//                 disabled={processing}
//                 className="btn btn-success px-4 py-2"
//               >
//                 {processing ? "Importation..." : "Importer"}
//               </button>

//             </div>
//           </form>

//         </div>
//       </div>
//     </div>
//   );
// }