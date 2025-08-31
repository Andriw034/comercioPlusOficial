
// Import the functions you need from the SDKs you need
import { initializeApp, getApps, getApp } from "firebase/app";
import { getAuth, connectAuthEmulator } from "firebase/auth";
import { getStorage } from "firebase/storage";
import { initializeFirestore, connectFirestoreEmulator, getFirestore } from "firebase/firestore";
import { config } from 'dotenv';

config();

// Your web app's Firebase configuration
const firebaseConfig = {
  apiKey: process.env.NEXT_PUBLIC_FIREBASE_API_KEY,
  authDomain: "comercio-plus.firebaseapp.com",
  projectId: "comercio-plus",
  storageBucket: "comercio-plus.appspot.com",
  messagingSenderId: "655361811812",
  appId: "1:655361811812:web:bf1178f63ed9e8c4620671",
  measurementId: "G-8SYJPQVVZ4"
};

// Initialize Firebase
const app = !getApps().length ? initializeApp(firebaseConfig) : getApp();
const auth = getAuth(app);
const storage = getStorage(app);

// Use getFirestore() to avoid multiple instances in Next.js server/client components
const db = getFirestore(app);

if (process.env.NODE_ENV === 'development' && typeof window !== 'undefined') {
    // This check is important to run this code only in the browser
    // and in development mode
    console.log("Development environment (client-side): Connecting to emulators");
    try {
        connectAuthEmulator(auth, "http://localhost:9099", { disableWarnings: true });
        connectFirestoreEmulator(db, "localhost", 8080);
    } catch (e) {
        console.error("Error connecting to emulators. Are they running?", e);
    }
} else if (process.env.NODE_ENV === 'development') {
    console.log("Development environment (server-side): Emulators will be connected on client.");
}

export { app, db, auth, storage };
