const {test}=require('node:test'),assert=require('node:assert/strict');
const {keyFor,readDraft,writeDraft,assignmentIds}=require('./booking-draft.cjs');
test('Shop/actor draft survives remount without crossing identities or retaining reset booking',()=>{
 const map=new Map(),storage={getItem:k=>map.get(k),setItem:(k,v)=>map.set(k,v),removeItem:k=>map.delete(k)};
 const state={services:[{id:101,master:{id:102,service_master:{id:101}}}],dateAndTimes:[]};
 writeDraft(storage,keyFor('shop-101',108),state);
 assert.deepEqual(readDraft(storage,keyFor('shop-101',108)),state);
 assert.equal(readDraft(storage,keyFor('shop-102',108)),null);
 assert.equal(readDraft(storage,keyFor('shop-101',101)),null);
 assert.deepEqual(assignmentIds(state.services),[101]);
 assert.deepEqual(assignmentIds([{id:101}]),[]);
 writeDraft(storage,keyFor('shop-101',108),{services:[],dateAndTimes:[]});
 assert.equal(readDraft(storage,keyFor('shop-101',108)),null);
});
test('malformed draft cannot produce an assignment or break the live context',()=>{
 assert.equal(readDraft({getItem:()=>'{bad'},'x'),null);
 assert.equal(readDraft({getItem:()=>JSON.stringify({services:[{id:'101'}],dateAndTimes:[]})},'x'),null);
 assert.doesNotThrow(()=>writeDraft({setItem:()=>{throw Error('blocked')}},'x',{services:[{id:101}]}));
});