
import { db } from '@/lib/firebase';
import { Store, StoreSchema } from '@/lib/schemas/store';
import { collection, doc, getDoc, setDoc, serverTimestamp, getDocs, query, where, limit } from 'firebase/firestore';

const storesCollection = collection(db, 'stores');

// Helper para crear un ID de tienda seguro a partir del nombre
const createSlug = (name: string) => {
    return name
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)+/g, '');
};
  

export async function createOrUpdateStore(userId: string, data: Partial<Omit<Store, 'id' | 'userId' | 'createdAt' | 'updatedAt'>>): Promise<void> {
    const storeRef = doc(db, 'stores', userId);
    const storeSnap = await getDoc(storeRef);

    if (storeSnap.exists()) {
        // Update existing store
        await setDoc(storeRef, { 
            ...data, 
            updatedAt: serverTimestamp() 
        }, { merge: true });
    } else {
        // Create new store
        const newStore: Omit<Store, 'id'> = {
            userId,
            name: data.name || "Nueva Tienda",
            slug: data.slug || createSlug(data.name || "nueva-tienda"),
            logo: data.logo || null,
            cover: data.cover || null,
            description: data.description || "",
            address: data.address || "",
            phone: data.phone || null,
            status: 'active',
            openingHours: data.openingHours || null,
            mainCategory: data.mainCategory || "Repuestos",
            averageRating: 0,
            theme: {
                primaryColor: '#FFA14F',
                backgroundColor: '#FFFFFF',
            },
            createdAt: serverTimestamp() as any, // Firestore will replace this
            updatedAt: serverTimestamp() as any, // Firestore will replace this
        };
        await setDoc(storeRef, newStore);
    }
}

export async function getStoreByUserId(userId: string): Promise<Store | null> {
    const storeRef = doc(db, 'stores', userId);
    const storeSnap = await getDoc(storeRef);

    if (!storeSnap.exists()) {
        return null;
    }

    const data = storeSnap.data();
    // Convert Firestore Timestamps to JS Dates
    const storeData = {
        ...data,
        id: storeSnap.id,
        createdAt: data.createdAt?.toDate(),
        updatedAt: data.updatedAt?.toDate(),
    } as Store;

    // Validate with Zod
    return StoreSchema.parse(storeData);
}

export async function getStoreBySlug(slug: string): Promise<Store | null> {
    const q = query(storesCollection, where("slug", "==", slug), limit(1));
    const querySnapshot = await getDocs(q);
  
    if (querySnapshot.empty) {
      return null;
    }
  
    const storeDoc = querySnapshot.docs[0];
    const data = storeDoc.data();
    const storeData = {
      ...data,
      id: storeDoc.id,
      createdAt: data.createdAt?.toDate(),
      updatedAt: data.updatedAt?.toDate(),
    } as Store;
    
    return StoreSchema.parse(storeData);
}

    