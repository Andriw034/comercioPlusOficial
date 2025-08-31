
// Import the functions you need from the SDKs you need
import { initializeApp, getApps, getApp } from "firebase/app";
import { getAuth, connectAuthEmulator } from "firebase/auth";
import { getStorage } from "firebase/storage";
import { initializeFirestore, connectFirestoreEmulator } from "firebase/firestore";
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
// Use initializeFirestore instead of getFirestore for better SSR compatibility
const db = initializeFirestore(app, {
  // Disable persistence on the server
  localCache: undefined,
});

if (process.env.NODE_ENV === 'development') {
    console.log('Development environment: Connecting to emulators');
    try {
        connectAuthEmulator(auth, "http://127.0.0.1:9099", { disableWarnings: true });
        connectFirestoreEmulator(db, "127.0.0.1", 8080);
    } catch (error) {
        console.error("Error connecting to emulators:", error);
    }
}

export { app, db, auth, storage };
